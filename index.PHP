<?php
require_once 'db.php';


if (isset($_POST['submit'])) {
    $name = trim($_POST['name'] ?? '');
    $age  = intval($_POST['age'] ?? 0);

    if ($name !== '' && $age > 0) {
        $stmt = $conn->prepare("INSERT INTO users (name, age, status) VALUES (?, ?, 0)");
        $stmt->bind_param("si", $name, $age);
        $stmt->execute();
        $stmt->close();
    }
   
    header("Location: index.php");
    exit;
}


$rows = [];
$res = $conn->query("SELECT id, name, age, status FROM users ORDER BY id DESC");
if ($res) {
    while ($r = $res->fetch_assoc()) { $rows[] = $r; }
    $res->free();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>نموذج + جدول + تبديل الحالة</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { font-family: Tahoma, Arial, sans-serif; margin: 24px; }
  h1 { margin-top: 0; }
  form.inline {
    display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
    margin-bottom: 16px;
  }
  input[type="text"], input[type="number"] {
    padding: 8px; border: 1px solid #ccc; border-radius: 6px; min-width: 180px;
  }
  input[type="submit"], button {
    padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;
    background: #f6f6f6;
  }
  table { border-collapse: collapse; width: 100%; max-width: 700px; }
  th, td { border: 1px solid #e0e0e0; padding: 10px; text-align: center; }
  th { background: #fafafa; }
  .badge {
    display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 13px;
    border: 1px solid #ddd;
  }
  .badge.on  { }
  .badge.off { opacity: 0.8; }
</style>
<script>
// تبديل الحالة عبر AJAX باستخدام fetch
async function toggleStatus(id, btn) {
  try {
    // تعطيل الزر مؤقتاً لمنع النقرات المتكررة
    btn.disabled = true;

    const res = await fetch('toggle.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'id=' + encodeURIComponent(id)
    });

    if (!res.ok) throw new Error('Network error');

    const data = await res.json(); // نتوقع {ok:true, status:0/1}
    if (data && data.ok) {
      const cell = document.getElementById('status-' + id);
      cell.textContent = data.status; // نعرض 0 أو 1
      // خيار تجميلي: تغيير كلاس الشارة
      cell.className = 'badge ' + (data.status == 1 ? 'on' : 'off');
    } else {
      alert('لم يتم التبديل. تأكدي من toggle.php');
    }
  } catch (e) {
    alert('خطأ أثناء التبديل: ' + e.message);
  } finally {
    btn.disabled = false;
  }
}
</script>
</head>
<body>

<h1>Welcome</h1>


<form class="inline" method="POST" action="index.php">
  <input type="text"   name="name" placeholder="name" required>
  <input type="number" name="age"  placeholder="age" required min="1" max="120">
  <input type="submit" name="submit" value="Submit">
</form>


<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>name</th>
      <th>age</th>
      <th>Status (0/1)</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($rows)) { ?>
      <tr><td colspan="5"there is no rcored yet </td></tr>
    <?php } else { foreach($rows as $row) { ?>
      <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['age']) ?></td>
        <td>
          <span id="status-<?= $row['id'] ?>"
                class="badge <?= $row['status'] ? 'on' : 'off' ?>">
                <?= $row['status'] ?>
          </span>
        </td>
        <td>
          <button onclick="toggleStatus(<?= $row['id'] ?>, this)">Toggle</button>
        </td>
      </tr>
    <?php } } ?>
  </tbody>
</table>

</body>
</html>