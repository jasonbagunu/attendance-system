<?php
require 'db.php';

$date_filter = $_GET['date'] ?? date('Y-m-d'); // default today

$stmt = $conn->prepare("SELECT a.id, a.student_id, s.name, s.course, a.scan_time 
                        FROM attendance a 
                        LEFT JOIN students s ON a.student_id = s.student_id
                        WHERE DATE(a.scan_time) = ?
                        ORDER BY a.scan_time DESC");
$stmt->bind_param("s", $date_filter);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Attendance Log</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding:20px;background:#071127;color:#fff;">
<div class="container">
  <h1>Attendance on <?php echo htmlspecialchars($date_filter); ?></h1>
  <form method="GET" class="mb-3">
    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
    <button class="btn btn-secondary">Filter</button>
    <a href="export.php?date=<?php echo htmlspecialchars($date_filter); ?>" class="btn btn-success">Export CSV</a>
    <a href="index.php" class="btn btn-light">Back</a>
  </form>

  <table class="table table-dark table-striped">
    <thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Course</th><th>Time</th></tr></thead>
    <tbody>
      <?php while ($r = $res->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($r['id']); ?></td>
          <td><?php echo htmlspecialchars($r['student_id']); ?></td>
          <td><?php echo htmlspecialchars($r['name']); ?></td>
          <td><?php echo htmlspecialchars($r['course']); ?></td>
          <td><?php echo htmlspecialchars($r['scan_time']); ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
