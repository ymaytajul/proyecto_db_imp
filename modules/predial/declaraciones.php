<?php
// modules/predial/declaraciones.php
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();

$codigo_contrib = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$anio_filtro = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// Variables de vista
$contribuyente = null;
$dj_actual = null;
$predios = [];
$anios_disponibles = [];

if ($db && $codigo_contrib) {
    try {
        // 1. Datos Contribuyente
        $stmt_c = $db->prepare("SELECT * FROM imp_contribuyente WHERE codigo = :cod");
        $stmt_c->execute([':cod' => $codigo_contrib]);
        $contribuyente = $stmt_c->fetch(PDO::FETCH_ASSOC);

        if ($contribuyente) {
            // 2. Buscar DJ del año seleccionado
            $stmt_dj = $db->prepare("
                SELECT d.*, m.denominacion as motivo 
                FROM imp_declaracion_jurada d
                JOIN imp_motivo_dj m ON d.id_motivo_dj = m.id_motivo_dj
                WHERE d.codigo_contribuyente = :cod AND d.anio = :anio AND d.estado != 'anulado'
                ORDER BY d.id_declaracion_jurada DESC LIMIT 1
            ");
            $stmt_dj->execute([':cod' => $codigo_contrib, ':anio' => $anio_filtro]);
            $dj_actual = $stmt_dj->fetch(PDO::FETCH_ASSOC);

            // 3. Si existe DJ, Buscar Predios
            if ($dj_actual) {
                // JOIN con imp_predio para obtener nombre/direccion si no está en snapshot
                $sql_p = "SELECT dp.*, up.descripcion_uso, p.nombre_predio
                          FROM imp_dj_predio dp
                          LEFT JOIN imp_uso_predio up ON dp.id_uso_predio = up.id_uso
                          LEFT JOIN imp_predio p ON dp.id_predio = p.id_predio
                          WHERE dp.id_declaracion_jurada = :id_dj 
                          ORDER BY dp.id_dj_predio ASC";
                $stmt_p = $db->prepare($sql_p);
                $stmt_p->execute([':id_dj' => $dj_actual['id_declaracion_jurada']]);
                $predios = $stmt_p->fetchAll(PDO::FETCH_ASSOC);
            }

            // 4. Años disponibles para selector
            $stmt_a = $db->prepare("SELECT DISTINCT anio FROM imp_declaracion_jurada WHERE codigo_contribuyente = :cod ORDER BY anio DESC");
            $stmt_a->execute([':cod' => $codigo_contrib]);
            $anios_disponibles = $stmt_a->fetchAll(PDO::FETCH_COLUMN);
        }
    } catch (Exception $e) {
        $error = "Error DB: " . $e->getMessage();
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid pt-3">

    <!-- Barra Superior de Navegación del Contribuyente -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 text-uppercase fw-bold text-primary">
            <i class="fas fa-file-invoice py-1"></i> Declaración Jurada (HR)
        </h1>
        <div class="btn-toolbar">
            <a href="contribuyentes.php" class="btn btn-sm btn-secondary me-2"><i class="fas fa-arrow-left"></i> Volver
                a Padrón</a>
            <?php if ($contribuyente): ?>
                <a href="declaraciones_nuevo.php?codigo=<?php echo $codigo_contrib; ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-plus-circle"></i> Nueva DJ
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$contribuyente): ?>
        <div class="alert alert-warning">No se ha seleccionado un contribuyente válido o no existe.</div>
    <?php else: ?>

        <!-- CABECERA DE DATOS (Estilo Resumen) -->
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                <span>RESUMEN DEL CONTRIBUYENTE</span>

                <!-- Selector de Año -->
                <form method="GET" class="d-flex align-items-center" style="gap: 5px;">
                    <input type="hidden" name="codigo" value="<?php echo $codigo_contrib; ?>">
                    <label class="mb-0 text-white small">Año Fiscal:</label>
                    <select name="anio" onchange="this.form.submit()" class="form-select form-select-sm py-0"
                        style="width: auto; height: 25px;">
                        <?php if (empty($anios_disponibles))
                            echo "<option>$anio_filtro</option>"; ?>
                        <?php foreach ($anios_disponibles as $a): ?>
                            <option value="<?php echo $a; ?>" <?php echo $a == $anio_filtro ? 'selected' : ''; ?>><?php echo $a; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body bg-light py-2">
                <div class="row g-2 text-uppercase small">
                    <div class="col-md-4">
                        <span class="fw-bold text-secondary">Contribuyente:</span><br>
                        <span
                            class="fs-6 text-dark fw-bold"><?php echo $contribuyente['codigo'] . ' - ' . $contribuyente['nombres_razon_social']; ?></span>
                    </div>
                    <div class="col-md-2">
                        <span class="fw-bold text-secondary">Doc. Identidad:</span><br>
                        <span class="text-dark"><?php echo $contribuyente['dni'] ?: $contribuyente['ruc']; ?></span>
                    </div>
                    <div class="col-md-2">
                        <span class="fw-bold text-secondary">Teléfono:</span><br>
                        <span class="text-dark"><?php echo $contribuyente['celular'] ?: '-'; ?></span>
                    </div>
                    <!-- Datos Financieros Globales (simulados o de la DJ actual) -->
                    <div class="col-md-2">
                        <span class="fw-bold text-secondary">Base Imponible:</span><br>
                        <span class="fs-6 fw-bold text-primary">S/
                            <?php echo number_format($dj_actual['total_base_imponible'] ?? 0, 2); ?></span>
                    </div>
                    <div class="col-md-2">
                        <span class="fw-bold text-secondary">Impuesto Anual:</span><br>
                        <span class="fs-6 fw-bold text-danger">S/
                            <?php echo number_format($dj_actual['impuesto_anual'] ?? 0, 2); ?></span>
                    </div>
                </div>
                <?php if ($dj_actual): ?>
                    <div class="mt-2 border-top pt-1 text-muted small px-1">
                        <i class="fas fa-info-circle"></i> DJ N° <?php echo $dj_actual['id_declaracion_jurada']; ?> |
                        Motivo: <?php echo $dj_actual['motivo']; ?> |
                        Fecha: <?php echo $dj_actual['fecha_declaracion']; ?> |
                        Estado: <span class="badge bg-success py-0"><?php echo $dj_actual['estado']; ?></span>
                    </div>
                <?php else: ?>
                    <div class="mt-2 text-danger small border-top pt-1">
                        <i class="fas fa-exclamation-triangle"></i> No existe Declaración Jurada para el año
                        <?php echo $anio_filtro; ?>.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CUERPO: DETALLE DE PREDIOS -->
        <?php if ($dj_actual): ?>
            <div class="card shadow-sm border">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-bold text-secondary text-uppercase"><i class="fas fa-home"></i> Relación de Predios</h6>
                    <div>
                        <a href="predios_nuevo.php?id_dj=<?php echo $dj_actual['id_declaracion_jurada']; ?>"
                            class="btn btn-sm btn-primary btn-action">
                            <i class="fas fa-plus"></i> Agregar Predio
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-condensed table-bordered mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th class="text-center" width="50">#</th>
                                <th width="100">Cód. Predio</th>
                                <th>Ubicación / Dirección Predio</th>
                                <th width="120">Uso</th>
                                <th class="text-end" width="100">Área (m2)</th>
                                <th class="text-end" width="120">Autovalúo</th>
                                <th class="text-center" width="100">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($predios) > 0): ?>
                                <?php foreach ($predios as $i => $row): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i + 1; ?></td>
                                        <td class="fw-bold"><?php echo str_pad($row['id_predio'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td>
                                            <div class="small fw-bold"><?php echo $row['nombre_predio']; ?></div>
                                            <div class="text-muted small"><?php echo $row['direccion_predio']; ?></div>
                                        </td>
                                        <td><?php echo $row['descripcion_uso']; ?></td>
                                        <td class="text-end"><?php echo number_format($row['area_terreno'], 2); ?></td>
                                        <td class="text-end fw-bold">S/ <?php echo number_format($row['total_autoavaluo'], 2); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white btn-action"><i
                                                    class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-danger btn-action"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No se han registrado predios en esta
                                        declaración.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-light fw-bold small">
                            <tr>
                                <td colspan="5" class="text-end text-uppercase">Total Base Imponible:</td>
                                <td class="text-end text-primary fs-6">S/
                                    <?php echo number_format($dj_actual['total_base_imponible'], 2); ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>