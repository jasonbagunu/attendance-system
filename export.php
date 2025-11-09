<?php
require 'db.php';

$date = $_GET['date'] ?? date('Y-m-d');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_' . $date . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Student ID', 'Name', 'Course', 'Scan Time']);

$stmt = $conn->prepare("SELECT a.student_id, s.name, s.course, a.scan_time
                        FROM attendance a
                        LEFT JOIN students s ON a.student_id = s.student_id
                        WHERE DATE(a.scan_time) = ?
                        ORDER BY a.scan_time ASC");
$stmt->bind_param("s", $date);
$stmt->execute();
$res = $stmt->get_result();

while ($r = $res->fetch_assoc()) {
    fputcsv($out, [$r['student_id'], $r['name'], $r['course'], $r['scan_time']]);
}
fclose($out);
exit;
