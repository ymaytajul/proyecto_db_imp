<?php
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_edit = isset($_POST['is_edit']) && $_POST['is_edit'] == '1';

    // Recoger datos
    $codigo = $_POST['codigo'];
    $tipo_persona = $_POST['tipo_persona'];
    $dni = $_POST['dni'];
    $ruc = $_POST['ruc'];
    $nombres = $_POST['nombres_razon_social'];
    $telefono = $_POST['telefono_fijo'];
    $celular = $_POST['celular'];
    $correo = $_POST['correo_electronico'];
    $observaciones = $_POST['observaciones'];
    $estado = $_POST['estado'];

    try {
        if ($is_edit) {
            // Update
            $sql = "UPDATE imp_contribuyente SET 
                    tipo_persona = :tipo_persona, 
                    dni = :dni, 
                    ruc = :ruc, 
                    nombres_razon_social = :nombres,
                    telefono_fijo = :telefono,
                    celular = :celular,
                    correo_electronico = :correo,
                    observaciones = :observaciones
                    WHERE codigo = :codigo";
        } else {
            // Insert
            $sql = "INSERT INTO imp_contribuyente 
                    (codigo, estado, tipo_persona, dni, ruc, nombres_razon_social, telefono_fijo, celular, correo_electronico, observaciones, fecha_creacion)
                    VALUES 
                    (:codigo, :estado, :tipo_persona, :dni, :ruc, :nombres, :telefono, :celular, :correo, :observaciones, CURRENT_DATE)";
        }

        $stmt = $pdo->prepare($sql);

        $params = [
            ':codigo' => $codigo,
            ':tipo_persona' => $tipo_persona,
            ':dni' => $dni,
            ':ruc' => $ruc,
            ':nombres' => $nombres,
            ':telefono' => $telefono,
            ':celular' => $celular,
            ':correo' => $correo,
            ':observaciones' => $observaciones
        ];

        if (!$is_edit) {
            $params[':estado'] = $estado;
        }

        $stmt->execute($params);

        // Redirect success
        header("Location: contribuyentes.php?msg=saved");
        exit;

    } catch (PDOException $e) {
        die("Error al guardar: " . $e->getMessage());
    }
} else {
    header("Location: contribuyentes.php");
    exit;
}
?>