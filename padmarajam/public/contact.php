<?php
session_start();
include '../includes/db.php'; // adjust as needed
$conn = getDbConnection();

// Fetch site settings once
$settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
$siteLevelsettings = $settingsRes->fetch_assoc();

$message = '';

// Form submission handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CAPTCHA
    $captcha_input = strtoupper(trim($_POST['captcha_input'] ?? ''));
    $captcha_code = $_SESSION['captcha'] ?? '';

    if ($captcha_input !== $captcha_code) {
        $message = "❌ Invalid CAPTCHA. Please try again.";
    } else {
        unset($_SESSION['captcha']);

        $name    = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email   = htmlspecialchars(trim($_POST['email'] ?? ''));
        $msg     = htmlspecialchars(trim($_POST['message'] ?? ''));

        if (!$name || !$email || !$msg) {
            $message = "❌ All fields are required.";
        } else {
            $stmt = $conn->prepare("INSERT INTO enquiries (name, email, message) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sss", $name, $email, $msg);
                if ($stmt->execute()) {
                    // ✅ Send email to admin
                    $adminEmail = $settings['contact_email']; // replace with real admin email
                    $subject = "New Contact Form Submission";
                    $body = "You have received a new message from the contact form:\n\n"
                          . "Name: $name\n"
                          . "Email: $email\n"
                          . "Message:\n$msg\n";
                    $headers = "From: admin@example.com\r\n" .  // use a real domain
                               "Reply-To: $email\r\n" .
                               "Content-Type: text/plain; charset=UTF-8\r\n";

                    @mail($adminEmail, $subject, $body, $headers); // Suppress errors with @

                    $message = "✅ Your message has been sent successfully!";
                } else {
                    $message = "❌ Failed to save message. Please try again.";
                }
                $stmt->close();
            } else {
                $message = "❌ Database error: " . $conn->error;
            }
        }
    }
}
// Include shared header
include '_header.php';
?>

  <script src="./assets/js/captcha.js"></script>
  <script>
    // Generate CAPTCHA on page load
    $(document).ready(function() {
      generateCaptcha();
    });
  </script>

<main class="container bg-white text-gray-800 shadow">
  <div class="mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6 text-center">Contact Us</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <!-- Left: Address and Map -->
      <div class="bg-white rounded p-6 flex flex-col gap-4">
        <div>
          <h2 class="text-lg font-semibold mb-2 ">Address</h2>
          <p class="mb-2">
            <?= nl2br(htmlspecialchars($siteLevelsettings['address'] ?? '123 Main Street, Your City, Country')) ?>
          </p>
          <p class="mb-2 flex gap-6">
           <span> <strong>Phone:</strong> <a class="hover:underline" href="tel:<?= htmlspecialchars($siteLevelsettings['contact_no'] ?? '99999 99999') ?>"><?= htmlspecialchars($siteLevelsettings['contact_no'] ?? '99999 99999') ?></a></span>
            <span><strong>Email:</strong> <a class="hover:underline" href="mailto:<?= htmlspecialchars($siteLevelsettings['contact_email'] ?? 'info@example.com') ?>"><?= htmlspecialchars($siteLevelsettings['contact_email'] ?? 'info@example.com') ?></a></span>
          </p>
        </div>
        <div>
          <h2 class="text-lg font-semibold mb-2 ">Location Map</h2>
          <div class="w-full google-map-frame rounded overflow-hidden">
           

                <?php
                  // Output Google Map code as raw HTML (not escaped)
                  if (!empty($settings['google_map'])) {
                      echo $settings['google_map'];
                  }
                  ?>

            
          </div>
        </div>
      </div>
      <!-- Right: Contact Form -->
      <div>
        <?php if ($message): ?>
          <p class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded"><?= $message ?></p>
        <?php endif; ?>
        

        <form method="post" class="bg-white p-6 rounded">
           <h2 class="text-lg font-semibold mb-2 ">Enquiry</h2>

          <div class="mb-4">
            <label>Name:</label>
            <input type="text" name="name" required class="w-full border p-2 rounded" />
          </div>
          <div class="mb-4">
            <label>Email:</label>
            <input type="email" name="email" required class="w-full border p-2 rounded" />
          </div>
          <div class="mb-4">
            <label>Message:</label>
            <textarea name="message" required class="w-full h-32 border p-2 rounded"></textarea>
          </div>

          <!-- CAPTCHA -->
          <div class="mb-4">
            <label>CAPTCHA:</label>
            <div class="flex items-center space-x-4">
              <div id="captchaText" class="font-mono text-lg bg-gray-200 px-4 py-2 rounded select-none"></div>
              <button type="button" onclick="generateCaptcha()" class="text-sm text-blue-600 hover:underline">↻ Refresh</button>
            </div>
            <input type="text" name="captcha_input" id="captcha_input" placeholder="Enter Captcha text" required class="mt-2 w-60 border p-2 rounded uppercase tracking-widest" />
          </div>

          <button type="submit" class="bg-primary text-white px-4 py-3 rounded w-full mt-2">
            Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</main>


<?php include '_footer.php'; ?>
