<?php
// تحميل الإعدادات العامة
require_once __DIR__ . '/config.php';

// تحميل ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/db.php';

// بدء الجلسة
session_start();

// التحقق أن المستخدم أدمن
if (!is_admin()) {
    // تحويل غير الأدمن لصفحة تسجيل دخول الأدمن
    header('Location: admin_login.php');
    exit;
}

// الاتصال بقاعدة البيانات
$pdo = db();

// جلب آخر 200 طلب من قاعدة البيانات
$orders = $pdo
    ->query("SELECT * FROM orders ORDER BY id DESC LIMIT 200")
    ->fetchAll();

// دالة لحماية النصوص قبل الطباعة
function h($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>لوحة الطلبات (مسؤول)</title>
  <link rel="stylesheet" href="assets/style.css" />
</head>

<body>

<header class="container">
  <h1>📦 الطلبات</h1>

  <nav>
    <a href="index.php">المنتجات</a>
    <a href="cart.php">العربة</a>
    <a href="orders.php">الطلبات</a>
    <a href="admin_logout.php">خروج (مسؤول)</a>
  </nav>
</header>

<main class="container">

  <?php if (!$orders): ?>
    <!-- في حال لا توجد طلبات -->
    <p>لا توجد طلبات بعد.</p>

  <?php else: ?>

    <!-- جدول عرض الطلبات -->
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>التاريخ</th>
          <th>الحالة</th>
          <th>المبلغ</th>
          <th>العميل</th>
          <th>طريقة</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <!-- رقم الطلب -->
            <td><?= (int)$o['id']; ?></td>

            <!-- تاريخ الطلب -->
            <td><?= h($o['created_at']); ?></td>

            <!-- حالة الطلب -->
            <td><?= h($o['status']); ?></td>

            <!-- المبلغ -->
            <td>
              <?= number_format((float)$o['amount'], 2); ?>
              <?= h($o['currency']); ?>
            </td>

            <!-- معلومات العميل -->
            <td>
              <?= h($o['customer_name']); ?>
              /
              <?= h($o['customer_phone']); ?>
            </td>

            <!-- طريقة الاستلام -->
            <td><?= h($o['delivery_method']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php endif; ?>
</main>

</body>
</html>
