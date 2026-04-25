<?php
session_start();
include '../includes/db.php';
$conn = getDbConnection();

// Restrict to admin only
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch current settings
$settingsRes = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $settingsRes->fetch_assoc() ?? [];
$uploadDir = '../uploads/';
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'company_name', 'contact_no', 'whatsapp_no', 'contact_email', 'header_message',
        'address', 'website', 'google_map', 'fb', 'instagram', 'linkedin',
        'x', 'youtube', 'meta_title', 'meta_description', 'meta_keywords', 'google_analytics', 'banner_images'
    ];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $conn->real_escape_string($_POST[$field] ?? '');
    }

    // Handle logo upload
    if (!empty($_FILES['logo']['name'])) {
        $logo = basename($_FILES['logo']['name']);
        $targetPath = '../uploads/' . $logo;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
            $data['logo'] = $logo;
        }
    }
	
	// Handle favicon upload
    if (!empty($_FILES['favicon']['name'])) {
        $favicon = basename($_FILES['favicon']['name']);
        $targetPath = '../uploads/' . $favicon;
        if (move_uploaded_file($_FILES['favicon']['tmp_name'], $targetPath)) {
            $data['favicon'] = $favicon;
        }
    }
	// Handle banner images upload
	if (isset($_FILES['banner_images']) &&
    is_array($_FILES['banner_images']['name']) &&
    count(array_filter($_FILES['banner_images']['name'])) > 0)
		{	
		foreach ($_FILES['banner_images']['tmp_name'] as $key => $tmpName) {
			if ($_FILES['banner_images']['error'][$key] === UPLOAD_ERR_OK) {
				$name = basename($_FILES['banner_images']['name'][$key]);
				$uploadedFilename =  uniqid() . '_' . $name;
				$targetPath = $uploadDir . $uploadedFilename;
				
				if (move_uploaded_file($tmpName, $targetPath)) {
					$uploadedImages[] = $uploadedFilename;
				}
			}
		}
		$json = json_encode($uploadedImages);
		$data['banner_images'] = $json;
		}
    // Insert or update
    if ($settings) {
        $updates = [];
        foreach ($data as $key => $value) {
			if(!empty($value) || 1) {
				$updates[] = "$key = '$value'";
				}
			}
        $conn->query("UPDATE settings SET " . implode(', ', $updates));
    } else {
        $cols = implode(', ', array_keys($data));
        $vals = "'" . implode("','", $data) . "'";
        $conn->query("INSERT INTO settings ($cols) VALUES ($vals)");
    }

    header("Location: settings.php?updated=1");
    exit;
}
include '_header.php';
?>

 <main class="p-6 mt-16 space-y-4">

    <div class="wrapper max-w-4xl mx-auto">

 


 <section class="overflow-x-auto bg-white shadow rounded-lg p-6">

<div class="mx-auto bg-white rounded">
    <?php if (isset($_GET['updated'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">✅ Settings updated successfully.</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Company Details -->
        <div class="md:col-span-2"><h3 class="font-semibold text-lg border-b pb-2">Company Details</h3></div>
        <div>
            <label class="block mb-1">Company Name</label>
            <input type="text" name="company_name" value="<?= htmlspecialchars($settings['company_name'] ?? '') ?>" class="w-full border p-2 rounded" required>
        </div>

        <div>
            <label class="block mb-1">Contact Email</label>
            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

         <div>
            <label class="block mb-1">Contact No</label>
            <input type="text" name="contact_no" value="<?= htmlspecialchars($settings['contact_no'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block mb-1">WhatsApp No</label>
            <input type="text" name="whatsapp_no" value="<?= htmlspecialchars($settings['whatsapp_no'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        
        <div class="md:col-span-2">
            <label class="block mb-1">Website Link</label>
            <input type="text" name="website" value="<?= htmlspecialchars($settings['website'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        
		<div class="logoblock fileInput">
            <label class="block mb-1">Logo</label>
            <input type="file" name="logo" accept="image/*" class="w-full">
            <?php if (!empty($settings['logo'])): ?>
                <img src="../uploads/<?= $settings['logo'] ?>" alt="Logo" class="w-20 mt-2">
            <?php endif; ?>
        </div>
        
		<div class="faviconblock fileInput">
            <label class="block mb-1">Favicon</label>
            <input type="file" name="favicon" accept="image/*" class="w-full">
            <?php if (!empty($settings['favicon'])): ?>
                <img src="../uploads/<?= $settings['favicon'] ?>" alt="Favicon" class="w-20 mt-2">
            <?php endif; ?>
        </div>

		<!-- <div class="bannerImgblock">
		<div>
		<label class="block mb-1">Upload Banner Images</label>
		<input type="file" name="banner_images[]" multiple><br><br>
		</div>
		<div class="current-images">
		 <label class="block mb-1">Current Images:</label>

         <div class="flex flex-wrap gap-2">

		  <?php
		  // Show current images
		  $res = $conn->query("SELECT banner_images FROM settings");
		  $row = $res->fetch_assoc();
		  $images = json_decode($row['banner_images'], true);

		  if (!empty($images)) {
			  foreach ($images as $img) {
				  $imgFilepath = $uploadDir.basename($img);
				  echo "<img src='$imgFilepath' width='150' style='margin:5px;' />";
			  }
		  } else {
			  echo "No images uploaded.";
		  }
		  ?>
          </div>
		</div>
		</div>  -->

       
		<!-- <div class="md:col-span-2">
            <label class="block mb-1">Header Message</label>
            <textarea name="header_message" rows="2" class="w-full border p-2 rounded"><?= htmlspecialchars($settings['header_message'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block mb-1">Address</label>
            <textarea name="address" rows="2" class="w-full border p-2 rounded"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block mb-1">Google Map Embed Link</label>
            <textarea name="google_map" rows="2" class="w-full border h-40 p-2 rounded"><?= htmlspecialchars($settings['google_map'] ?? '') ?></textarea>
        </div> -->

        <!-- Social Media -->
        <div class="md:col-span-2 mt-4"><h3 class="font-semibold text-lg border-b pb-2">Social Media</h3></div>
        <div><label class="block mb-1">Facebook</label>
            <input type="text" name="fb" value="<?= htmlspecialchars($settings['fb'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        <div><label class="block mb-1">Instagram</label>
            <input type="text" name="instagram" value="<?= htmlspecialchars($settings['instagram'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        <div><label class="block mb-1">LinkedIn</label>
            <input type="text" name="linkedin" value="<?= htmlspecialchars($settings['linkedin'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        <div><label class="block mb-1">X (Twitter)</label>
            <input type="text" name="x" value="<?= htmlspecialchars($settings['x'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        <div><label class="block mb-1">YouTube</label>
            <input type="text" name="youtube" value="<?= htmlspecialchars($settings['youtube'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>

        <!-- SEO Settings -->
        <div class="md:col-span-2 mt-4"><h3 class="font-semibold text-lg border-b pb-2">SEO Settings</h3></div>
        <div class="md:col-span-2">
            <label class="block mb-1">Meta Title</label>
            <input type="text" name="meta_title" value="<?= htmlspecialchars($settings['meta_title'] ?? '') ?>" class="w-full border p-2 rounded">
        </div>
        <div class="md:col-span-2">
            <label class="block mb-1">Meta Description</label>
            <textarea name="meta_description" rows="2" class="w-full border p-2 rounded"><?= htmlspecialchars($settings['meta_description'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block mb-1">Meta Keywords</label>
            <textarea name="meta_keywords" rows="2" class="w-full border p-2 rounded"><?= htmlspecialchars($settings['meta_keywords'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block mb-1">Google Analytics Code</label>
            <textarea name="google_analytics" rows="4" class="w-full h-40 border p-2 rounded"><?= htmlspecialchars($settings['google_analytics'] ?? '') ?></textarea>
        </div>

        <div class="md:col-span-2 text-right mt-4">
           


            <button type="submit" class="bg-primary font-semibold uppercase text-white px-4 py-2 rounded w-auto mr-4">
                       Save Settings
                    </button>
                    <button type="button" class="bg-gray-500 font-semibold uppercase text-white px-4 py-2 rounded w-auto" onclick="window.location.href='dashboard.php'">
                        Cancel
                    </button>
        </div>




    </form>
</div>
</section>
</div>
</main>

<?php include '_footer.php'; ?>