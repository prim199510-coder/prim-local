<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

$settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsRes->fetch_assoc();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($admin_id, $hashed_password);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin_id;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
     <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" >
    <link rel="stylesheet" href="../assets/admin/css/custom.css">

   
</head>
<body>
<div class="bg-gray-100 w-full h-screen flex items-center justify-center">
    <div class="flex bg-white shadow-lg overflow-hidden w-full mx-auto">
        <div class="hidden lg:block lg:w-1/2 bg-cover login-bg-cover">
        </div>

        <div class="w-full h-screen p-4 sm:px-16 sm:py-20 lg:w-1/2 flex flex-col justify-center">

                <div class="login-form">
                    <div class="flex">
                        <img src="../uploads/<?= htmlspecialchars($settings['logo']) ?>" alt="Logo" class="h-20">
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mt-4">Admin Login</h2>            
                    <form method="POST" class="max-w-md mt-6">
                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                            <input required autofocus  name="username" class="text-gray-700 focus:outline-none focus:shadow-outline border border-gray-300 rounded py-2 px-4 block w-full appearance-none" type="text" />
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-between">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                            </div>
                            <input required name="password" class="text-gray-700 focus:outline-none focus:shadow-outline border border-gray-300 rounded py-2 px-4 block w-full appearance-none" type="password" />
                        </div>
                        <?php if ($error): ?><p class="text-red-500 mt-4"><?= $error ?></p><?php endif; ?>
                        <div class="mt-8">
                            <button type="submit" class="bg-primary text-white font-bold py-2 px-4 w-full rounded hover:bg-red-600">Login</button>
                        </div>
                    </form>          
                </div>
            </div>

    </div>
</div>


</body>
</html>
