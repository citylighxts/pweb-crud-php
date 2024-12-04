<?php
$config = [
    'servername' => 'localhost',
    'username' => 'root',
    'password' => '',
    'dbname' => 'pweb-crud-php'
];

function createDatabaseConnection($config) {
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $conn = new mysqli(
            $config['servername'], 
            $config['username'], 
            $config['password'], 
            $config['dbname']
        );

        if ($conn->connect_error) {
            error_log("Database Connection Failed: " . $conn->connect_error);
            throw new Exception("Database connection failed. Please try again later.");
        }

        return $conn;
    } catch (Exception $e) {
        die("Connection error: " . $e->getMessage());
    }
}

$conn = createDatabaseConnection($config);
?>