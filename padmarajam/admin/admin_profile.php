<?php
session_start();
include '../includes/db.php';
$conn = getDbConnection();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('Location: login.php');
    exit;
}

// Fetch admin details
$result = $conn->query("SELECT username, email, contact FROM admins WHERE id = $admin_id");
$admin = $result->fetch_assoc();
?>

<?php include '_header.php'; ?>

<main class="p-6 mt-16 space-y-4">
  <div class="wrapper max-w-4xl mx-auto">
    <section class="overflow-x-auto bg-white shadow rounded-lg p-6">
      <div class="mx-auto bg-white rounded">
        <div id="message"></div>
        <h2 class="font-semibold text-lg border-b pb-2 mb-4">Personal Details</h2>

        <!-- Read-only Profile Info -->
        <form id="profileForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block mb-1">Admin Email:</label>
            <input type="email" name="email" class="w-full border p-2 rounded bg-gray-100" 
                   value="<?= htmlspecialchars($admin['email']) ?>" readonly>
          </div>
          <div>
            <label class="block mb-1">Admin Username:</label>
            <input type="text" name="username" class="w-full border p-2 rounded bg-gray-100" 
                   value="<?= htmlspecialchars($admin['username']) ?>" readonly>
          </div>
        </form>

        <!-- Password Change Section -->
        <h2 class="font-semibold text-lg border-b pb-2 mb-4 mt-8">Change Password</h2>

        <form id="passwordForm" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
          <label>Old Password:</label>
          <input type="password" name="old_password" required>

          <label>New Password:</label>
          <input type="password" name="new_password" required>

          <label>Confirm Password:</label>
          <input type="password" name="confirm_password" required>

          <div class="md:col-span-2 text-right">
            <button type="submit" class="bg-primary font-semibold uppercase text-white px-4 py-2 rounded w-auto mr-4">
              Update Password
            </button>
            <button type="button" class="bg-gray-500 font-semibold uppercase text-white px-4 py-2 rounded w-auto"
                    onclick="window.location.href='dashboard.php'">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</main>

<script>
function showMessage(type, msg) {
  const div = document.getElementById("message");
  div.textContent = msg;
  div.className = type;
  setTimeout(() => { div.textContent = ""; }, 3000);
}

document.getElementById("passwordForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append("action", "change_password");

  const res = await fetch("update_admin.php", { method: "POST", body: formData });
  const data = await res.json();

  if (data.success) showMessage("success", data.message);
  else showMessage("error", data.message);
});
</script>

<?php include '_footer.php'; ?>
