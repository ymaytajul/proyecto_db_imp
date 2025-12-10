<?php
// modules/predial/contribuyentes_nuevo.php (Backend Logic for Modal Form)
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$db)
        die("Error de conexión DB");

    try {
        $db->beginTransaction();

        $mode = $_POST['mode'] ?? 'new';
        $codigo = $_POST['codigo'] ?? '';

        $tipo_persona = $_POST['tipo_persona'];
        $nro_doc = $_POST['nro_doc'];
        $nombres = strtoupper($_POST['nombres']);
        $fecha_nac = !empty($_POST['fecha_nac']) ? $_POST['fecha_nac'] : null;
        $celular = $_POST['celular'];
        $correo = $_POST['correo'];

        // Docs logic
        $dni = ($tipo_persona == 'NATURAL') ? $nro_doc : null;
        $ruc = ($tipo_persona == 'JURIDICA') ? $nro_doc : null;

        if ($mode == 'new') {
            // Validar Duplicado
            $check = $db->prepare("SELECT codigo FROM imp_contribuyente WHERE (dni = ? AND dni IS NOT NULL) OR (ruc = ? AND ruc IS NOT NULL)");
            $check->execute([$dni, $ruc]);
            if ($check->rowCount() > 0)
                throw new Exception("Ya existe un contribuyente con ese Documento.");

            // Nuevo Codigo (C00000X)
            $row_max = $db->query("SELECT MAX(CAST(SUBSTRING(codigo FROM 2) AS INTEGER)) as max_cod FROM imp_contribuyente WHERE codigo LIKE 'C%'")->fetch();
            $next = ($row_max['max_cod'] ?? 0) + 1;
            $codigo = 'C' . str_pad($next, 6, '0', STR_PAD_LEFT);

            // Insertar Contribuyente
            $sql = "INSERT INTO imp_contribuyente (codigo, estado, tipo_persona, dni, ruc, nombres_razon_social, fecha_nacimiento, celular, correo_electronico, fecha_creacion)
                    VALUES (?, 'activo', ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$codigo, $tipo_persona, $dni, $ruc, $nombres, $fecha_nac, $celular, $correo]);

        } else {
            // Update
            $sql = "UPDATE imp_contribuyente SET tipo_persona=?, dni=?, ruc=?, nombres_razon_social=?, fecha_nacimiento=?, celular=?, correo_electronico=? 
                    WHERE codigo=?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tipo_persona, $dni, $ruc, $nombres, $fecha_nac, $celular, $correo, $codigo]);
        }

        // --- MANEJO DOMICILIO FISCAL ---
        // Verificamos si ya tiene registro
        $chk_dom = $db->prepare("SELECT id_domicilio_fiscal FROM imp_domicilio_fiscal_contribuyente WHERE codigo_contribuyente = ?");
        $chk_dom->execute([$codigo]);
        $exists_dom = $chk_dom->fetch();

        // Armar direccion string simple para visualización rápida
        // (En un sistema real seria mejor guardar componentes y armar view, aqui updateamos para que el grid se vea bien si usa ese campo)

        // Campos dom
        $id_via = !empty($_POST['id_via']) ? $_POST['id_via'] : null;
        $numero = $_POST['numero'];
        $interior = $_POST['interior'];
        $manzana = $_POST['manzana'];
        $lote = $_POST['lote'];
        $id_sector = !empty($_POST['id_sector']) ? $_POST['id_sector'] : null;
        $referencia = $_POST['referencia'];

        // Construir string dirección fiscal
        // Primero obtener nombres via/sector para string? (Opcional, pg triggers lo harían mejor)
        // Por simplicidad, guardamos componentes.

        if ($exists_dom) {
            $sql_dom = "UPDATE imp_domicilio_fiscal_contribuyente SET 
                        id_via=?, numero=?, numero_interior=?, manzana=?, lote=?, id_sector=?, referencia_direccion=?
                        WHERE codigo_contribuyente=?";
            $stmt_d = $db->prepare($sql_dom);
            $stmt_d->execute([$id_via, $numero, $interior, $manzana, $lote, $id_sector, $referencia, $codigo]);
        } else {
            $sql_dom = "INSERT INTO imp_domicilio_fiscal_contribuyente 
                        (codigo_contribuyente, estado, id_via, numero, numero_interior, manzana, lote, id_sector, referencia_direccion, id_distrito)
                        VALUES (?, 'activo', ?, ?, ?, ?, ?, ?, ?, 1)"; // Distrito 1 dummy
            $stmt_d = $db->prepare($sql_dom);
            $stmt_d->execute([$codigo, $id_via, $numero, $interior, $manzana, $lote, $id_sector, $referencia]);
        }

        // Update string direccion_fiscal en tabla padre si existe columna redundante (según grid query parece que hace join, no necesita update aqui)

        $db->commit();

        // Redirigir
        header("Location: contribuyentes.php?msg=" . urlencode("Contribuyente $codigo guardado exitosamente."));
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        die("Error al guardar: " . $e->getMessage() . " <a href='contribuyentes.php'>Volver</a>");
    }
} else {
    // Si entran por GET directo, mandar al listado
    header("Location: contribuyentes.php");
    exit;
}
?>