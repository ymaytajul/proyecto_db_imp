<?php
// modules/predial/contribuyentes_editar.php
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();
$message = "";
$error = "";

$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';

if (empty($codigo)) {
    header("Location: contribuyentes.php");
    exit;
}

// Cargar datos actuales
// Cargar datos actuales
try {
    if ($db) {
        $stmt_load = $db->prepare("SELECT * FROM imp_contribuyente WHERE codigo = :codigo");
        $stmt_load->execute([':codigo' => $codigo]);
        $contribuyente = $stmt_load->fetch(PDO::FETCH_ASSOC);

        if (!$contribuyente) {
            throw new Exception("Contribuyente no encontrado.");
        }
    } else {
        throw new Exception("No hay conexión a la base de datos.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Procesar Actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($db) {
        try {
            $nombres = strtoupper($_POST['nombres']);
            $fecha_nac = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
            $celular = $_POST['celular'];
            $correo = $_POST['correo'];
            $nro_doc = $_POST['nro_doc'];

            // Validación básica
            if (empty($nombres)) {
                throw new Exception("El Nombre/Razón Social es obligatorio.");
            }

            // Actualizar
            $sql_update = "UPDATE imp_contribuyente SET 
                nombres_razon_social = :nombres,
                fecha_nacimiento = :fecha_nac,
                celular = :celular,
                correo_electronico = :correo,
                dni = :dni,
                ruc = :ruc
                WHERE codigo = :codigo";

            // Determinar si actualizamos DNI o RUC basado en el tipo original (que no cambia)
            $dni = ($contribuyente['tipo_persona'] == 'NATURAL') ? $nro_doc : null;
            $ruc = ($contribuyente['tipo_persona'] == 'JURIDICA') ? $nro_doc : null;

            $stmt = $db->prepare($sql_update);
            $stmt->execute([
                ':nombres' => $nombres,
                ':fecha_nac' => $fecha_nac,
                ':celular' => $celular,
                ':correo' => $correo,
                ':dni' => $dni,
                ':ruc' => $ruc,
                ':codigo' => $codigo
            ]);

            $message = "Datos actualizados correctamente.";

            // Recargar datos
            $stmt_load->execute([':codigo' => $codigo]);
            $contribuyente = $stmt_load->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    } else {
        $error = "No hay conexión a la base de datos.";
    }
}

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Contribuyente: <?php echo htmlspecialchars($codigo); ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="contribuyentes.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Tipo Persona</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($contribuyente['tipo_persona']); ?>" readonly
                                disabled>
                        </div>
                        <div class="col-md-4">
                            <label for="nro_doc" class="form-label">DNI / RUC</label>
                            <input type="text" class="form-control" id="nro_doc" name="nro_doc"
                                value="<?php echo htmlspecialchars($contribuyente['dni'] ?? $contribuyente['ruc']); ?>"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_nac" class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nac" name="fecha_nac"
                                value="<?php echo htmlspecialchars($contribuyente['fecha_nacimiento']); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nombres" class="form-label">Apellidos y Nombres / Razón Social *</label>
                        <input type="text" class="form-control" id="nombres" name="nombres"
                            value="<?php echo htmlspecialchars($contribuyente['nombres_razon_social']); ?>" required
                            style="text-transform: uppercase;">
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="celular" class="form-label">Celular / WhatsApp</label>
                            <input type="text" class="form-control" id="celular" name="celular"
                                value="<?php echo htmlspecialchars($contribuyente['celular']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="correo" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo"
                                value="<?php echo htmlspecialchars($contribuyente['correo_electronico']); ?>">
                        </div>
                    </div>

                    <hr>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar
                            Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>