<?php
// Guard the function definition to avoid "Cannot redeclare" when this file
// is included multiple times (header + page includes).
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        $host = 'localhost';        // Replace with your actual DB host
        $user = 'root';     // Replace with your DB username
        $pass = ''; // Replace with your DB password
        $dbname = 'padmarajam';   // Replace with your DB name

        $conn = new mysqli($host, $user, $pass, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
?>
