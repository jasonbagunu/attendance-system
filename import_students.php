<?php
require 'db.php';

$msg = '';
$imported_names = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $file = $_FILES['csvfile']['tmp_name'];
    if (($handle = fopen($file, "r")) !== FALSE) {
        $row = 0;
        $conn->begin_transaction();
        try {
      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $row++;
        if ($row == 1) {
          // skip header if it looks like header
          $header = array_map('strtolower', $data);
          if (in_array('student_id', $header)) continue;
        }
        $sid = trim($data[0] ?? '');
        $name = trim($data[1] ?? '');
        $course = trim($data[2] ?? '');
        $year = intval($data[3] ?? 0);

        if ($sid === '' || $name === '') continue;

        $stmt = $conn->prepare("INSERT INTO students (student_id, name, course, year_level) VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE name=VALUES(name), course=VALUES(course), year_level=VALUES(year_level)");
        $stmt->bind_param("sssi", $sid, $name, $course, $year);
        $stmt->execute();
        $imported_names[] = $name;
      }
      $conn->commit();
      $msg = "Import finished.";
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "Import failed: " . $e->getMessage();
        }
        fclose($handle);
    } else {
        $msg = "Cannot open file.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Import Students</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1>Import Students via CSV</h1>
  <?php if ($msg): ?><div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <?php if (!empty($imported_names)): ?>
    <div class="alert alert-success">
      <strong>Imported students:</strong>
      <ul>
        <?php foreach ($imported_names as $n): ?>
          <li><?php echo htmlspecialchars($n); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-2">
      <input type="file" name="csvfile" accept=".csv" required>
    </div>
    <button class="btn btn-warning">Upload</button>
    <a href="students.php" class="btn btn-light">Back</a>
  </form>

  <div class="mt-3">
    <h5>CSV sample:</h5>
    <pre>student_id,name,course,year_level
2300247,Jason Bagunu,BSIT,3
2025002,Ana Cruz,BSIT,3</pre>
  </div>
</div>
</body>
</html>
