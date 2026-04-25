<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

$res = $conn->query("SELECT * FROM enquiries ORDER BY submitted_at DESC");

?>
<?php include '_header.php'; ?>




<main class="p-6 mt-16 space-y-4">
    <div class="flex justify-between mb-4">
        <h1 class="text-3xl font-bold">Contact Messages</h1>
    </div>

    <section class="overflow-x-auto bg-white shadow rounded-lg">
        <?php if ($res->num_rows > 0): ?>
    <table id="order-table" class="border table-fixed w-full">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-4 py-2 border w-20">#</th>
          <th class="px-4 py-2 border">Name</th>
          <th class="px-4 py-2 border">Email</th>
          <th class="px-4 py-2 border w-1/3">Message</th>
          <th class="px-4 py-2 border">Date</th>
        </tr>
      </thead>
      <tbody>
		  <?php while ($row = $res->fetch_assoc()): ?>
		  <tr>
			<td class="border px-4 py-2"><?= $row['id'] ?></td>
			<td class="border px-4 py-2"><?= htmlspecialchars($row['name']) ?></td>
			<td class="border px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
			<td class="border px-4 py-2">
			  <div class="contMsg"><?= htmlspecialchars($row['message']) ?></div>
			</td>
			<td class="border px-4 py-2"><?= $row['submitted_at'] ?></td>
		  </tr>
		  <?php endwhile; ?>
	  </tbody>
    </table>
    <?php else: ?>
      <p>No messages found.</p>
    <?php endif; ?>
    </section>
</main>



<?php include '_footer.php'; ?>