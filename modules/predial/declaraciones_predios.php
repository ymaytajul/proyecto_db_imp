<?php
// modules/predial/declaraciones_predios.php
require_once '../../config/db.php';
require_once '../../includes/calculos.php';

$database = new Database();
$db = $database->getConnection();

$id_dj = isset($_GET['id_dj']) ? $_GET['id_dj'] : '';

if (empty($id_dj)) {
    header("Location: declaraciones.php");
    exit;
}

$dj = null;
$predios = [];
$impuesto_calculado = 0.00;
$total_autoavaluo = 0.00;

if ($db) {
    try {
        // 1. Obtener Predios primero para recalcular totales
        $stmt_p = $db->prepare("SELECT dp.*, up.descripcion_uso 
                                FROM imp_dj_predio dp
                                LEFT JOIN imp_uso_predio up ON dp.id_uso_predio = up.id_uso
                                WHERE dp.id_declaracion_jurada = :id ORDER BY dp.id_dj_predio ASC");
        $stmt_p->execute([':id' => $id_dj]);
        $predios = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

        // 2. Calcular Totales
        foreach ($predios as $p) {
            $total_autoavaluo += $p['total_autoavaluo'];
        }

        // 3. Obtener Cabecera
        $stmt_head = $db->prepare("SELECT d.*, c.nombres_razon_social, c.codigo as cod_contrib 
                                   FROM imp_declaracion_jurada d 
                                   JOIN imp_contribuyente c ON d.codigo_contribuyente = c.codigo
                                   WHERE d.id_declaracion_jurada = :id");
        $stmt_head->execute([':id' => $id_dj]);
        $dj = $stmt_head->fetch(PDO::FETCH_ASSOC);

        if (!$dj)
            die("Declaración no encontrada.");

        // 4. Calcular Impuesto
        $calc = new CalculadoraPredial($db, $dj['anio']);
        $impuesto_calculado = $calc->calcularImpuesto($total_autoavaluo);
        $impuesto_trimestral = $impuesto_calculado / 4;

        // 5. Actualizar Cabecera si hay cambios
        if (abs($dj['total_base_imponible'] - $total_autoavaluo) > 0.01 || abs($dj['impuesto_anual'] - $impuesto_calculado) > 0.01) {
            $stmt_upd = $db->prepare("UPDATE imp_declaracion_jurada SET 
                total_predios_declarados = :cant,
                total_base_imponible = :base,
                impuesto_anual = :imp,
                impuesto_trimestral = :trim
                WHERE id_declaracion_jurada = :id");
            $stmt_upd->execute([
                ':cant' => count($predios),
                ':base' => $total_autoavaluo,
                ':imp' => $impuesto_calculado,
                ':trim' => $impuesto_trimestral,
                ':id' => $id_dj
            ]);
            // Refrescar datos en memoria
            $dj['total_base_imponible'] = $total_autoavaluo;
            $dj['impuesto_anual'] = $impuesto_calculado;
        }

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    $error_msg = "No hay conexión a la base de datos.";
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid pt-3">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h1 class="h4 text-uppercase fw-bold text-dark"><i class="fas fa-file-invoice-dollar"></i> Hoja Resumen (HR)
            #<?php echo $dj['id_declaracion_jurada']; ?></h1>
        <div class="btn-group">
            <a href="declaraciones.php?codigo=<?php echo $dj['codigo_contribuyente']; ?>"
                class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver a Declaraciones
            </a>
            <a href="print_hr.php?id=<?php echo $id_dj; ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print"></i> Imprimir HR
            </a>
        </div>
    </div>

    <!-- Cabecera Informativa -->
    <div class="card mb-3 shadow-sm border-0 bg-light">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <small class="text-muted d-block text-uppercase">Contribuyente</small>
                    <span class="fw-bold fs-5 text-primary"><?php echo $dj['cod_contrib']; ?> -
                        <?php echo $dj['nombres_razon_social']; ?></span>
                </div>
                <div class="col-md-2 text-center border-start border-end">
                    <small class="text-muted d-block text-uppercase">Año Fiscal</small>
                    <span class="badge bg-dark fs-6"><?php echo $dj['anio']; ?></span>
                </div>
                <div class="col-md-5">
                    <div class="row text-end">
                        <div class="col-6">
                            <small class="text-muted d-block text-uppercase">Total Autoavalúo</small>
                            <span class="fs-5 fw-bold">S/
                                <?php echo number_format($dj['total_base_imponible'], 2); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block text-uppercase">Impuesto Anual</small>
                            <span class="fs-5 fw-bold text-danger">S/
                                <?php echo number_format($dj['impuesto_anual'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Predios -->
    <div class="card shadow-sm border">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0 fw-bold text-uppercase text-secondary">Predios Declarados</h6>
            <a href="predios_nuevo.php?id_dj=<?php echo $id_dj; ?>" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-plus-circle"></i> Nuevo Predio
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered table-sm mb-0">
                <thead class="table-dark small text-uppercase">
                    <tr>
                        <th width="5%" class="text-center">ID</th>
                        <th width="40%">Ubicación del Predio</th>
                        <th width="15%">Uso</th>
                        <th width="10%" class="text-end">A. Terr.</th>
                        <th width="10%" class="text-end">A. Const.</th>
                        <th width="10%" class="text-end">Valor (S/)</th>
                        <th width="10%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php if (!empty($predios)): ?>
                        <?php foreach ($predios as $row): ?>
                            <tr>
                                <td class="text-center fw-bold text-secondary"><?php echo $row['id_dj_predio']; ?></td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    <?php echo htmlspecialchars($row['direccion_predio']); ?>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['descripcion_uso'] ?? 'S/N'); ?></span></td>
                                <td class="text-end font-monospace"><?php echo number_format($row['area_terreno'], 2); ?></td>
                                <td class="text-end font-monospace"><?php echo number_format($row['total_area_construida'], 2); ?></td>
                                <td class="text-end fw-bold font-monospace">S/ <?php echo number_format($row['total_autoavaluo'], 2); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="predios_nuevo.php?id_predio=<?php echo $row['id_dj_predio']; ?>&id_dj=<?php echo $id_dj; ?>" class="btn btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                        <a href="print_pu.php?id=<?php echo $row['id_dj_predio']; ?>" target="_blank" class="btn btn-outline-info" title="Imprimir PU"><i class="fas fa-print"></i></a>
                                        <button class="btn btn-outline-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-home fa-3x mb-3 d-block text-secondary"></i>
                                No hay predios registrados. Agregue uno para comenzar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>