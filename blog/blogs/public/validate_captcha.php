<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

header('Content-Type: application/json');

$input = strtoupper(trim($_POST['captcha_input'] ?? ''));
$stored = $_SESSION['captcha'] ?? '';

echo "input: ".$input . ":::: stored: ".$stored;

if ($input !== '' && $stored !== '' && $input === $stored) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}*/
//<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

//ob_start(); // Buffer output

session_start();

header('Content-Type: application/json');

// Log everything into a string
//$log = "";

// Debug: input + session
$input = strtoupper(trim($_REQUEST['captcha_input'] ?? ''));
$stored = $_SESSION['captcha'] ?? '';

//$log .= "Input: $input\nStored: $stored\n";

if ($input !== '' && $stored !== '' && $input === $stored) {
    $result = ['success' => true];
} else {
    $result = ['success' => false];
}

echo json_encode($result);

// Show any unwanted output that breaks JSON
//$debug = ob_get_clean();
//if (strlen($debug) > 0) {
//    file_put_contents(__DIR__ . '/debug_output.log', $debug);
//}

?>
