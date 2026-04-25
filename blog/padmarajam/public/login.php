<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM users WHERE email='$email'");
    $user = $res->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: orders.php");
        exit;
    } else {
        $error = "Invalid login!";
    }
}
?>

<form method="POST">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p>$error</p>"; ?>
    <input name="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Password" required><br>
    <button>Login</button>
</form>
