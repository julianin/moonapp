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
$accion = $_POST['accion'];
$token = $_POST['token'];

// Get USER_ID corresponding to the token
$sql_get_user_id = "SELECT USER_ID FROM SESSION_LOG WHERE SLOG_TOKEN = '$token'";
$result = $conn->query($sql_get_user_id);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['USER_ID'];
} else {
    echo "Invalid token";
    exit();
}

// Handle logout action
if ($accion === 'logout' && !empty($token)) {
    // Get current datetime
    $now = date('Y-m-d H:i:s');

    // Insert new record into SESSION_LOG table with USER_ID
    $sql_insert_session = "INSERT INTO SESSION_LOG (SLOG_FECHA, SLOG_STATE, SLOG_TOKEN, USER_ID) VALUES ('$now', '0', '$token', '$user_id')";
    if ($conn->query($sql_insert_session) === TRUE) {
        // Session log inserted successfully
        // Update USER_SLOG to 0 for corresponding USER_ID in USER table
        $sql_update_user_slog = "UPDATE USER SET USER_SLOG = '0' WHERE USER_ID = '$user_id'";
        if ($conn->query($sql_update_user_slog) === TRUE) {
            echo "Logout successful";
        } else {
            echo "Error updating USER_SLOG: " . $conn->error;
        }
    } else {
        // Error inserting session log
        echo "Error inserting record into SESSION_LOG: " . $conn->error;
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>
