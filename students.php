<?php
require 'db.php';

$msg = '';

// Delete student
// Delete all students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_students'])) {
  if ($conn->query("DELETE FROM students")) {
    $msg = "All students deleted.";
  } else {
    $msg = "Error deleting all students.";
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
  $del_id = trim($_POST['delete_student']);
  if ($del_id !== '') {
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $del_id);
    if ($stmt->execute()) $msg = "Student deleted.";
    else $msg = "Error deleting student.";
  }
}

// Add student manually
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $sid = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $course = trim($_POST['course']);
    $year = intval($_POST['year_level'] ?? 0);

    if ($sid !== '' && $name !== '') {
        $stmt = $conn->prepare("INSERT INTO students (student_id, name, course, year_level) VALUES (?, ?, ?, ?) 
                                ON DUPLICATE KEY UPDATE name=VALUES(name), course=VALUES(course), year_level=VALUES(year_level)");
        $stmt->bind_param("sssi", $sid, $name, $course, $year);
        if ($stmt->execute()) $msg = "Student saved.";
        else $msg = "Error saving student.";
    } else {
        $msg = "Student ID and name required."; 
    }
}

// Fetch students
$res = $conn->query("SELECT * FROM students ORDER BY name ASC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Students</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> body{padding:20px;background:#071127;color:#fff;} .card{background:#08121a;border:1px solid #b8872a;} </style>
</head>
<body>
<div class="container">
  <h1>Students</h1>

  <?php if ($msg): ?><div class="alert alert-info"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

  <div class="card p-3 mb-3">
    <h5>Add / Update Student</h5>
    <form method="POST">
      <div class="row">
        <div class="col-md-3 mb-2">
          <input type="text" name="student_id" class="form-control" placeholder="Student ID (must match barcode)" required>
        </div>
        <div class="col-md-4 mb-2">
          <input type="text" name="name" class="form-control" placeholder="Full name" required>
        </div>
        <div class="col-md-3 mb-2">
          <input type="text" name="course" class="form-control" placeholder="Course">
        </div>
        <div class="col-md-2 mb-2">
          <input type="number" name="year_level" class="form-control" placeholder="Year">
        </div>
      </div>
      <button class="btn btn-warning">Save</button>
      <a href="import_students.php" class="btn btn-secondary">Import CSV</a>
      <a href="index.php" class="btn btn-light">Back to Scanner</a>
      <form method="POST" style="display:inline;" onsubmit="return confirm('Delete ALL students? This cannot be undone!');">
        <input type="hidden" name="delete_all_students" value="1">
        <button class="btn btn-danger" style="margin-left:10px;"></button>
      </form>
    </form>
  </div>

  <div class="card p-3">
    <h5>Student List</h5>
    <table class="table table-sm table-dark">
      <thead>
        <tr><th>ID</th><th>Name</th><th>Course</th><th>Year</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php while ($r = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['student_id']); ?></td>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><?php echo htmlspecialchars($r['course']); ?></td>
            <td><?php echo htmlspecialchars($r['year_level']); ?></td>
            <td>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this student?');">
                <input type="hidden" name="delete_student" value="<?php echo htmlspecialchars($r['student_id']); ?>">
                <button class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
