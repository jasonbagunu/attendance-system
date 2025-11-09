<?php
require 'db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['barcode'])) {
  $barcode = trim($_POST['barcode']);
  // Remove first two and last digit
  if (strlen($barcode) > 3) {
    $barcode = substr($barcode, 2, -1);
  }

  // Basic validation
  if ($barcode === '') {
    $msg = "Empty barcode.";
  } else {
    // Check student exists
    $stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
      $msg = "Unknown ID: " . htmlspecialchars($barcode) . " (scanned: " . htmlspecialchars(trim($_POST['barcode'])) . ")";
    } else {
      $row = $res->fetch_assoc();
      $name = $row['name'];

      // Check if already marked today
      $stmt2 = $conn->prepare("SELECT id, scan_time FROM attendance WHERE student_id = ? AND DATE(scan_time) = CURDATE() LIMIT 1");
      $stmt2->bind_param("s", $barcode);
      $stmt2->execute();
      $res2 = $stmt2->get_result();

      if ($res2->num_rows > 0) {
        $existing = $res2->fetch_assoc();
        $msg = "Already recorded for {$name} at " . $existing['scan_time'];
      } else {
        $stmt3 = $conn->prepare("INSERT INTO attendance (student_id) VALUES (?)");
        $stmt3->bind_param("s", $barcode);
        if ($stmt3->execute()) {
          $msg = "✅ Attendance recorded for " . htmlspecialchars($name) . " at " . date("Y-m-d H:i:s");
        } else {
          $msg = "Error saving attendance.";
        }
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Attendance Scanner</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 30px; background:#0f172a; color:#e6e6e6; }
    .card { background: #111827; border: 1px solid #b8872a; }
    input { background:#0b1220; color:#fff; }
  </style>
</head>
<body>
<div class="container">
  <h1 class="mb-3">Attendance Scanner</h1>

  <div class="card p-3 mb-3">
    <form method="POST" id="scanForm" autocomplete="off">
      <div class="mb-2">
        <label for="barcode" class="form-label">Scan Student ID</label>
        <input type="text" id="barcode" name="barcode" class="form-control form-control-lg" placeholder="Scan here..." autofocus autocomplete="off" />
      </div>
      <div>
        <button type="submit" class="btn btn-warning">Submit</button>
        <a href="students.php" class="btn btn-secondary">Students</a>
        <a href="attendance.php" class="btn btn-secondary">Attendance Log</a>
        <a href="export.php" class="btn btn-success">Export CSV</a>
      </div>
    </form>
    <div class="mt-3">
      <?php if ($msg): ?>
        <div class="alert alert-light"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  const barcodeInput = document.getElementById('barcode');
  window.onload = () => { barcodeInput.focus(); };
  window.addEventListener('click', () => barcodeInput.focus());

  // Optional: clear input after submit (if scanner sends Enter it reloads page, so keep this)
  const form = document.getElementById('scanForm');
  form.addEventListener('submit', () => {
    // small delay to allow form to submit
    setTimeout(() => { barcodeInput.value = ''; barcodeInput.focus(); }, 500);
  });
</script>
</body>
</html>
