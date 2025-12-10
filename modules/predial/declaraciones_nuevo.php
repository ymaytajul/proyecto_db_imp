<?php
// modules/predial/declaraciones_nuevo.php
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();

// --- 1. Obtener Listas Maestras ---
$motivos = [];
if ($db) {
    try {
        $motivos = $db->query("SELECT * FROM imp_motivo_dj ORDER BY denominacion")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { /* Silent */
    }
}

// --- 2. Procesar Guardado ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_guardar'])) {
    if ($db) {
        try {
            $codigo_contrib = $_POST['codigo_contribuyente'];
            $anio = $_POST['anio'];
            $id_motivo = $_POST['id_motivo'];
            $fecha = $_POST['fecha_declaracion'];

            // Verificar contribuyente valido
            $st_ver = $db->prepare("SELECT codigo FROM imp_contribuyente WHERE codigo = ?");
            $st_ver->execute([$codigo_contrib]);
            if ($st_ver->rowCount() == 0)
                throw new Exception("Código de contribuyente inválido.");

            $sql_ins = "INSERT INTO imp_declaracion_jurada 
                (codigo_contribuyente, id_motivo_dj, anio, fecha_declaracion, estado, total_predios_declarados, total_base_imponible, impuesto_anual)
                VALUES (:cod, :mot, :anio, :fecha, 'activo', 0, 0.00, 0.00)";

            $st_ins = $db->prepare($sql_ins);
            $st_ins->execute([
                ':cod' => $codigo_contrib,
                ':mot' => $id_motivo,
                ':anio' => $anio,
                ':fecha' => $fecha
            ]);

            $id_dj = $db->lastInsertId();
            // Redirigir al Dashboard de la DJ
            header("Location: declaraciones.php?codigo=" . $codigo_contrib);
            exit;

        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "No hay conexión a la base de datos.";
    }
}

// --- 3. Búsqueda Contribuyente y Pre-selección ---
// Si viene ?codigo=X desde el dashboard, lo preseleccionamos
$pre_cod = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$pre_name = '';

if ($pre_cod && $db) {
    $st = $db->prepare("SELECT nombres_razon_social FROM imp_contribuyente WHERE codigo = ?");
    $st->execute([$pre_cod]);
    $pre_name = $st->fetchColumn();
}

$busqueda_resultado = null;
if (isset($_GET['search_term']) && $db) {
    try {
        $term = "%" . $_GET['search_term'] . "%";
        $st_b = $db->prepare("SELECT codigo, dni, nombres_razon_social FROM imp_contribuyente WHERE estado!='anulado' AND (nombres_razon_social LIKE ? OR codigo LIKE ? OR dni LIKE ?) LIMIT 10");
        $st_b->execute([$term, $term, $term]);
        $busqueda_resultado = $st_b->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { /* Silent */
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid pt-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 fw-bold text-uppercase"><i class="fas fa-file-contract"></i> Nueva Declaración Jurada</h1>
        <a href="contribuyentes.php" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
    </div>

    <div class="row g-3">
        <!-- Panel Izquierdo: Selección de Contribuyente -->
        <div class="col-md-5">
            <div class="card shadow-sm h-100 border">
                <div class="card-header bg-dark text-white py-1">
                    <span class="small fw-bold text-uppercase">1. Buscar Contribuyente</span>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search_term" class="form-control"
                                placeholder="Nombre, DNI o Código..." required
                                value="<?php echo htmlspecialchars($_GET['search_term'] ?? ''); ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                        <?php if (!empty($pre_cod))
                            echo '<input type="hidden" name="codigo" value="' . $pre_cod . '">'; ?>
                    </form>

                    <?php if ($busqueda_resultado): ?>
                        <div class="list-group list-group-flush fs-6">
                            <?php foreach ($busqueda_resultado as $c): ?>
                                <a href="#" class="list-group-item list-group-item-action py-2 px-2"
                                    onclick="seleccionarContribuyente('<?php echo $c['codigo']; ?>', '<?php echo htmlspecialchars($c['nombres_razon_social']); ?>')">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong class="text-primary small"><?php echo $c['codigo']; ?></strong>
                                        <small class="text-muted"><?php echo $c['dni']; ?></small>
                                    </div>
                                    <div class="small text-truncate"><?php echo $c['nombres_razon_social']; ?></div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (isset($_GET['search_term'])): ?>
                        <div class="text-muted small text-center mt-3">No se encontraron resultados.</div>
                    <?php else: ?>
                        <div class="alert alert-info py-2 small mb-0">Use el buscador para encontrar un contribuyente si no
                            está seleccionado.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel Derecho: Datos de la DJ -->
        <div class="col-md-7">
            <div class="card shadow-sm h-100 border">
                <div class="card-header bg-primary text-white py-1">
                    <span class="small fw-bold text-uppercase">2. Datos de la Declaración</span>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Contribuyente Seleccionado</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                <input type="text" id="display_nombre" class="form-control bg-white fw-bold" readonly
                                    value="<?php echo $pre_cod ? "$pre_cod - $pre_name" : ''; ?>"
                                    placeholder="<-- Busque y seleccione un contribuyente">
                                <input type="hidden" name="codigo_contribuyente" id="codigo_contribuyente"
                                    value="<?php echo $pre_cod; ?>" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Año Fiscal *</label>
                                <input type="number" name="anio"
                                    class="form-control form-control-sm text-center fw-bold"
                                    value="<?php echo date('Y'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Fecha Emisión *</label>
                                <input type="date" name="fecha_declaracion" class="form-control form-control-sm"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Motivo de Declaración *</label>
                            <select name="id_motivo" class="form-select form-select-sm" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($motivos as $m): ?>
                                    <option value="<?php echo $m['id_motivo_dj']; ?>">
                                        <?php echo $m['codigo'] . ' - ' . $m['denominacion']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr class="my-3">
                        <button type="submit" name="btn_guardar" class="btn btn-success w-100 fw-bold">
                            <i class="fas fa-save"></i> GENERAR HOJA RESUMEN (HR)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function seleccionarContribuyente(codigo, nombre) {
        document.getElementById('codigo_contribuyente').value = codigo;
        document.getElementById('display_nombre').value = codigo + ' - ' + nombre;
    }
</script>

<?php include '../../includes/footer.php'; ?>