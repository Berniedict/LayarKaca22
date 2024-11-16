<?php
// Database configuration
$host = '127.0.0.1';        // Your database host (usually 'localhost')
$db_name = 'layarkaca22';   // Your database name
$db_user = 'root';          // Your database username
$db_password = '';          // Your database password

try {
    // Create a new PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_password);
    
    // Set PDO attributes for error handling and secure interactions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>
