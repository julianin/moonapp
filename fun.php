<?php
//SETTINGS SETUP PERMISOS Y ERRORES
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
error_reporting(E_ALL);
ini_set('display_errors', '1');

//BBDD SETUP
$servername = "192.168.1.238:3306";
$database = "moonapp";
$username = "user";
$password = "sadm";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve POST data
$USER = $_POST['USER'];
$PASS = $_POST['PASS'];

// Generate random token
$token = bin2hex(random_bytes(16)); // 32 characters in hexadecimal

// Perform SQL query to validate user credentials
$sql = "SELECT * FROM USER WHERE USER_PASS = '$PASS' AND USER_NOMBRE = '$USER' AND USER_FECHA_BAJA IS NULL";
$result = $conn->query($sql);

if ($result !== false && $result->num_rows > 0) {
    // Valid credentials
    $user_row = $result->fetch_assoc();
    $user_codigo = (int)$user_row['USER_ID']; // Convertir a entero
    $now = date('Y-m-d H:i:s');

    // Insert new record into SESSION_LOG table
    $sql_insert = "INSERT INTO SESSION_LOG (USER_ID, SLOG_FECHA, SLOG_STATE, SLOG_TOKEN) VALUES ('$user_codigo', '$now', '1', '$token')";
    if ($conn->query($sql_insert) === TRUE) {
        // Session log inserted successfully
        set_time_limit(2);
        //actualizar USER y poner USER_SLOG a 1 donde USER_ID = $user_codigo
        $sql_update = "UPDATE USER SET USER_SLOG = '1' WHERE USER_ID = '$user_codigo'";
        $conn->query($sql_update);
        echo "OK - $token";
    } else {
        // Error inserting session log
        echo "Error al insertar registro en SESSION_LOG: " . $conn->error;
    }
} else {
    // Invalid credentials or error in query
    echo "Credenciales no válidas";
}

$conn->close();
?>
