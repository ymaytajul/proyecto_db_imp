<?php
// modules/predial/contribuyentes.php
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();

// --- Lógica de Filtros ---
$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$nombres = isset($_GET['nombres']) ? $_GET['nombres'] : '';
$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

$where = "WHERE estado != 'anulado'";
$params = [];

if (!empty($codigo)) {
    $where .= " AND codigo LIKE :codigo";
    $params[':codigo'] = "%$codigo%";
}
if (!empty($nombres)) {
    $where .= " AND nombres_razon_social LIKE :nombres";
    $params[':nombres'] = "%$nombres%";
}
if (!empty($dni)) {
    $where .= " AND (dni LIKE :dni OR ruc LIKE :dni)";
    $params[':dni'] = "%$dni%";
}

// --- Paginación Simple ---
$limit = 20;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$contribuyentes = [];
$total_rows = 0;

if ($db) {
    try {
        // Contar total
        $stmt_count = $db->prepare("SELECT COUNT(*) FROM imp_contribuyente $where");
        $stmt_count->execute($params);
        $total_rows = $stmt_count->fetchColumn();

        // Consulta Principal (CON DIRECCIÓN)
        // Concatenamos partes de la dirección si existen en la tabla real.
        // Asumimos que `direccion_fiscal` existe en tabla o la construimos. 
        // Viendo SQL: `imp_domicilio_fiscal_contribuyente` tiene la dirección, no `imp_contribuyente`.
        // JOIN implicito para traer la primera dirección fiscal activa.

        $sql = "SELECT c.codigo, c.dni, c.ruc, c.nombres_razon_social,
                       COALESCE(d.direccion_fiscal, 'Sin Domicilio Fiscal Registrado') as direccion
                FROM imp_contribuyente c
                LEFT JOIN imp_domicilio_fiscal_contribuyente d ON c.codigo = d.codigo_contribuyente
                $where
                ORDER BY c.codigo DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $contribuyentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error_msg = "Error DB: " . $e->getMessage();
    }
} else {
    $error_msg = "No hay conexión a la base de datos.";
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid pt-3">
    <!-- Header Módulo -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h5 text-uppercase fw-bold"><i class="fas fa-users"></i> Padrón de Contribuyentes</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#modalContribuyente" onclick="loadForm('')">
                <i class="fas fa-plus-circle"></i> Nuevo Contribuyente
            </button>
        </div>
    </div>

    <!-- Filtros - Barra Superior -->
    <div class="card mb-3 shadow-sm border-0 bg-light">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label mb-0">Código</label>
                    <input type="text" name="codigo" class="form-control form-control-sm"
                        value="<?php echo htmlspecialchars($codigo); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0">Apellidos / Razón</label>
                    <input type="text" name="nombres" class="form-control form-control-sm"
                        value="<?php echo htmlspecialchars($nombres); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-0">DNI / RUC</label>
                    <input type="text" name="dni" class="form-control form-control-sm"
                        value="<?php echo htmlspecialchars($dni); ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-primary px-3"><i class="fas fa-search"></i>
                        Buscar</button>
                    <a href="contribuyentes.php" class="btn btn-sm btn-secondary px-3"><i class="fas fa-eraser"></i>
                        Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger py-1 small"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- DataGrid -->
    <div class="card shadow-sm border">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-condensed table-bordered mb-0" id="gridContribuyentes">
                <thead class="bg-dark text-white">
                    <tr>
                        <th style="width: 100px;">Código</th>
                        <th style="width: 120px;">Doc. Identidad</th>
                        <th>Apellidos y Nombres / Razón Social</th>
                        <th>Dirección Fiscal</th>
                        <th class="text-center" style="width: 120px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contribuyentes)): ?>
                        <?php foreach ($contribuyentes as $row): ?>
                            <tr>
                                <td class="fw-bold text-primary"><?php echo $row['codigo']; ?></td>
                                <td><?php echo $row['dni'] ?: $row['ruc']; ?></td>
                                <td><?php echo $row['nombres_razon_social']; ?></td>
                                <td class="small text-muted"><?php echo $row['direccion']; ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info text-white btn-action" title="Editar"
                                        onclick="loadForm('<?php echo $row['codigo']; ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="declaraciones.php?codigo=<?php echo $row['codigo']; ?>"
                                        class="btn btn-sm btn-success btn-action" title="Ver Predios">
                                        <i class="fas fa-home"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger btn-action" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">No se encontraron registros.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Footer Paginación -->
        <div class="card-footer py-1 text-end">
            <small class="text-muted">Mostrando <?php echo count($contribuyentes); ?> de <?php echo $total_rows; ?>
                registros</small>
        </div>
    </div>
</div>

<!-- Modal Large (xl) para Formulario -->
<div class="modal fade" id="modalContribuyente" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" id="modalTitle">Gestión de Contribuyente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="modalBodyContent">
                <!-- Se carga vía AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Cargando formulario...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadForm(codigo) {
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBodyContent');

        if (codigo) {
            modalTitle.textContent = 'Editar Contribuyente: ' + codigo;
        } else {
            modalTitle.textContent = 'Nuevo Contribuyente';
        }

        // AJAX load
        modalBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        // Simular carga por ahora, llamar al archivo real
        fetch('form_contribuyente.php?codigo=' + codigo)
            .then(response => response.text())
            .then(html => {
                modalBody.innerHTML = html;
            })
            .catch(err => {
                modalBody.innerHTML = '<div class="alert alert-danger m-3">Error al cargar formulario.</div>';
            });

        // Abrir modal si no está abierto (manejado por data-bs-toggle, pero util para edit directo)
        var myModal = new bootstrap.Modal(document.getElementById('modalContribuyente'));
        myModal.show();
    }
</script>

<?php include '../../includes/footer.php'; ?>