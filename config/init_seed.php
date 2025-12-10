<?php
// config/init_seed.php
require_once 'db.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        try {
            // 1. Crear Schema
            $db->exec("CREATE SCHEMA IF NOT EXISTS imp;");
            $db->exec("SET search_path TO imp, public;");

            // 2. Ejecutar Estructura (Disabled to prevent overwrite error)
            // $sql_struct = file_get_contents(__DIR__ . '/../database.sql');
            // if ($sql_struct) {
            //     $db->exec($sql_struct);
            //     echo "Estructura de Base de Datos creada.\n";
            // }

            // 3. Ejecutar Semilla (seed_data.sql)
            $sql_seed = file_get_contents(__DIR__ . '/seed_data.sql');
            if ($sql_seed) {
                $db->exec($sql_seed);
                echo "Datos semilla insertados correctamente.\n";
            } else {
                echo "Error: No se encontró seed_data.sql\n";
            }

        } catch (PDOException $e) {
            echo "Error SQL: " . $e->getMessage();
        }
    } else {
        echo "Error: No hay conexión a la base de datos.";
    }
} catch (PDOException $e) {
    echo "Error al insertar datos: " . $e->getMessage();
}
?>