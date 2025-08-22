<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'db.php';

if (!isset($_POST['id'])) {
    echo json_encode(['ok'=>false, 'msg'=>'missing id']); exit;
}

$id = intval($_POST['id']);
if ($id <= 0) {
    echo json_encode(['ok'=>false, 'msg'=>'invalid id']); exit;
}

// قراءة الحالة الحالية
$stmt = $conn->prepare("SELECT status FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($current);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    echo json_encode(['ok'=>false, 'msg'=>'record not found']); exit;
}

// قلب القيمة
$newStatus = ($current == 1) ? 0 : 1;

// تحديث السجل
$u = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$u->bind_param("ii", $newStatus, $id);
$u->execute();
$u->close();

echo json_encode(['ok'=>true, 'status'=>$newStatus]);