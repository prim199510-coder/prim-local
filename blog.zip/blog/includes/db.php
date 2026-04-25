<?php
// Guard the function definition to avoid "Cannot redeclare" when this file
// is included multiple times (header + page includes).
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        $host = 'localhost';        // Replace with your actual DB host
        $user = 'padm_padmarajam_blog';     // Replace with your DB username
        $pass = 'Padmarajam@2988'; // Replace with your DB password
        $dbname = 'padm_padmarajam_blog';   // Replace with your DB name

        $conn = new mysqli($host, $user, $pass, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
?>
