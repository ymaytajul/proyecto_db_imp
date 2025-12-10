<?php
// test_db_connection.php
require_once 'config/db.php';

echo "--- Diagnostico de PHP ---\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Drivers PDO instalados: " . implode(", ", pdo_drivers()) . "\n";
echo "--------------------------\n";

if (!in_array("pgsql", pdo_drivers())) {
    echo "ERROR CRITICO: El driver 'pdo_pgsql' NO está instalado o habilitado.\n";
    echo "Debes editar c:\\xampp\\php\\php.ini y descomentar 'extension=pdo_pgsql'.\n";
    exit(1);
}

echo "Intentando conectar a PostgreSQL...\n";

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "¡EXITO! Conexión a la base de datos remota establecida correctamente.\n";

    try {
        $stmt = $conn->query("SELECT version()");
        $ver = $stmt->fetchColumn();
        echo "Versión de PostgreSQL remota: " . $ver . "\n";
    } catch (PDOException $e) {
        echo "Advertencia: Conexión OK, pero error al consultar versión: " . $e->getMessage() . "\n";
    }

} else {
    echo "FALLO: No se pudo conectar a la base de datos.\n";
    echo "Verifica las credenciales en config/db.php y que la IP 84.247.167.198 permita conexiones.\n";
}
?>