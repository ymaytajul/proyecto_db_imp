<?php
// modules/predial/form_contribuyente.php
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();

$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$data = [];

// Cargar Maestras (Filtros Vía, Sector)
$vias = [];
$sectores = [];

if ($db) {
    try {
        // Cargar Vías (Solo las primeras 50 para no saturar select combo)
        $vias = $db->query("SELECT id_via, nombre_via FROM imp_via LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        $sectores = $db->query("SELECT id_sector, nombre_sector FROM imp_sector LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($codigo)) {
            $stmt = $db->prepare("SELECT * FROM imp_contribuyente WHERE codigo = ?");
            $stmt->execute([$codigo]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // Cargar Domicilio Fiscal
            $stmt_dom = $db->prepare("SELECT * FROM imp_domicilio_fiscal_contribuyente WHERE codigo_contribuyente = ?");
            $stmt_dom->execute([$codigo]);
            $domicilio = $stmt_dom->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) { /* Silent fail for lists */
    }
}
?>

<form id="formContribuyente" class="p-3 bg-light" action="contribuyentes_nuevo.php" method="POST">
    <!-- Hidden fields for edit mode -->
    <?php if (!empty($codigo)): ?><input type="hidden" name="mode" value="edit"><input type="hidden" name="codigo"
            value="<?php echo $codigo; ?>"><?php endif; ?>

    <div class="row g-3">
        <!-- SECCIÓN 1: DATOS PERSONALES -->
        <div class="col-12">
            <h6 class="text-primary fw-bold border-bottom pb-1 mb-0">1. Identificación</h6>
        </div>

        <div class="col-md-3">
            <label class="form-label">Tipo Persona</label>
            <select class="form-select form-select-sm" name="tipo_persona">
                <option value="NATURAL" <?php echo ($data['tipo_persona'] ?? '') == 'NATURAL' ? 'selected' : ''; ?>>Persona
                    Natural</option>
                <option value="JURIDICA" <?php echo ($data['tipo_persona'] ?? '') == 'JURIDICA' ? 'selected' : ''; ?>>Persona
                    Jurídica</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">DNI / RUC</label>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" name="nro_doc"
                    value="<?php echo $data['dni'] ?? $data['ruc'] ?? ''; ?>" required>
                <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i> Reniec</button>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Apellidos y Nombres / Razón Social</label>
            <input type="text" class="form-control form-control-sm text-uppercase" name="nombres"
                value="<?php echo $data['nombres_razon_social'] ?? ''; ?>" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Teléfono / Celular</label>
            <input type="text" class="form-control form-control-sm" name="celular"
                value="<?php echo $data['celular'] ?? ''; ?>">
        </div>
        <div class="col-md-5">
            <label class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control form-control-sm" name="correo"
                value="<?php echo $data['correo_electronico'] ?? ''; ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Fecha Nacimiento</label>
            <input type="date" class="form-control form-control-sm" name="fecha_nac"
                value="<?php echo $data['fecha_nacimiento'] ?? ''; ?>">
        </div>

        <!-- SECCIÓN 2: DOMICILIO FISCAL -->
        <div class="col-12 mt-4">
            <h6 class="text-primary fw-bold border-bottom pb-1 mb-0">2. Domicilio Fiscal</h6>
        </div>

        <div class="col-md-4">
            <label class="form-label">Vía / Calle</label>
            <select class="form-select form-select-sm" name="id_via">
                <option value="">-- Seleccione --</option>
                <?php foreach ($vias as $v): ?>
                    <option value="<?php echo $v['id_via']; ?>" <?php echo ($domicilio['id_via'] ?? 0) == $v['id_via'] ? 'selected' : ''; ?>>
                        <?php echo $v['nombre_via']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Número</label>
            <input type="text" class="form-control form-control-sm" name="numero"
                value="<?php echo $domicilio['numero'] ?? ''; ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Interior</label>
            <input type="text" class="form-control form-control-sm" name="interior"
                value="<?php echo $domicilio['numero_interior'] ?? ''; ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Manzana</label>
            <input type="text" class="form-control form-control-sm" name="manzana"
                value="<?php echo $domicilio['manzana'] ?? ''; ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Lote</label>
            <input type="text" class="form-control form-control-sm" name="lote"
                value="<?php echo $domicilio['lote'] ?? ''; ?>">
        </div>

        <div class="col-md-4">
            <label class="form-label">Sector / Urb</label>
            <select class="form-select form-select-sm" name="id_sector">
                <option value="">-- Seleccione --</option>
                <?php foreach ($sectores as $s): ?>
                    <option value="<?php echo $s['id_sector']; ?>" <?php echo ($domicilio['id_sector'] ?? 0) == $s['id_sector'] ? 'selected' : ''; ?>>
                        <?php echo $s['nombre_sector']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control form-control-sm" name="referencia"
                value="<?php echo $domicilio['referencia_direccion'] ?? ''; ?>">
        </div>
    </div>

    <div class="modal-footer px-0 pb-0 mt-3 border-top-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Ficha</button>
    </div>
</form>