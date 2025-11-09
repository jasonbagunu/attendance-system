<?php
require 'db.php';


$date_filter = $_GET['date'] ?? date('Y-m-d'); // default today
$reset_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_today'])) {
  $stmt = $conn->prepare("DELETE FROM attendance WHERE DATE(scan_time) = ?");
  $stmt->bind_param("s", $date_filter);
  if ($stmt->execute()) {
    $reset_msg = "Attendance for $date_filter has been reset.";
  } else {
    $reset_msg = "Error resetting attendance.";
  }
}

$stmt = $conn->prepare("SELECT a.id, a.student_id, s.name, s.course, s.year_level, a.scan_time 
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1>Attendance on <?php echo htmlspecialchars($date_filter); ?></h1>
  <form method="GET" class="mb-3" style="display:inline-block;">
    <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>">
    <button class="btn btn-secondary">Filter</button>
    <a href="export.php?date=<?php echo htmlspecialchars($date_filter); ?>" class="btn btn-success">Export CSV</a>
    <a href="index.php" class="btn btn-light">Back</a>
  </form>
  <form method="POST" style="display:inline-block;margin-left:10px;" onsubmit="return confirm('Reset attendance for <?php echo htmlspecialchars($date_filter); ?>? This cannot be undone!');">
    <input type="hidden" name="reset_today" value="1">
    <button class="btn btn-danger">Reset Attendance</button>
  </form>
  <?php if ($reset_msg): ?>
    <div class="alert alert-warning mt-2"><?php echo htmlspecialchars($reset_msg); ?></div>
  <?php endif; ?>

  <table class="table table-dark table-striped">
    <thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Course</th><th>Year</th><th>Date</th><th>Time</th></tr></thead>
    <tbody>
      <?php while ($r = $res->fetch_assoc()): ?>
        <?php
          $date = date('Y-m-d', strtotime($r['scan_time']));
          $time = date('H:i:s', strtotime($r['scan_time']));
        ?>
        <tr>
          <td><?php echo htmlspecialchars($r['id']); ?></td>
          <td><?php echo htmlspecialchars($r['student_id']); ?></td>
          <td><?php echo htmlspecialchars($r['name']); ?></td>
          <td><?php echo htmlspecialchars($r['course']); ?></td>
          <td><?php echo htmlspecialchars($r['year_level']); ?></td>
          <td><?php echo htmlspecialchars($date); ?></td>
          <td><?php echo htmlspecialchars($time); ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
