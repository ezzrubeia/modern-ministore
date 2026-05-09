<?php
// تحميل إعدادات المشروع
require_once __DIR__ . '/config.php';

// تشغيل session إذا لم تكن شغالة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل ملفات قاعدة البيانات + الصلاحيات
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// قراءة العربة من الـ session (أو مصفوفة فاضية إذا لا يوجد)
$cart = $_SESSION['cart'] ?? [];

// إنشاء اتصال قاعدة البيانات
$pdo  = db();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <!-- ترميز يدعم العربية -->
  <meta charset="UTF-8" />

  <!-- دعم عرض مناسب للجوال -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- عنوان الصفحة -->
  <title>🧺 العربة</title>

  <!-- ملف CSS العام -->
  <link rel="stylesheet" href="assets/style.css" />
</head>
<body>

<!-- الهيدر العلوي -->
<header class="container">
  <h1>🧺 العربة</h1>

  <nav>
    <!-- روابط عامة -->
    <a href="index.php">المنتجات</a>
    <a href="cart.php">العربة</a>
    <a href="my_orders.php">طلباتي</a>

    <!-- روابط تظهر فقط إذا المستخدم أدمن -->
    <?php if (is_admin()): ?>
      <a href="admin_dashboard.php">لوحة التحكم (مسؤول)</a>
      <a href="orders.php">الطلبات</a>
      <a href="admin_logout.php">خروج المسؤول</a>
    <?php endif; ?>

    <!-- روابط حسب حالة تسجيل الدخول -->
    <?php if (is_logged_in()): ?>
      <span>مرحباً، <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
      <a href="user_logout.php">خروج</a>
    <?php else: ?>
      <a href="user_login.php">دخول</a>
      <a href="user_register.php">تسجيل</a>
    <?php endif; ?>
  </nav>
</header>

<main class="container">
<?php
// إذا العربة فارغة
if (!$cart) {

  echo '<p>🛒 عربتك فارغة.</p>';

} else {

  // مصفوفة تجهيز بيانات العرض
  $items = [];

  // إجمالي الطلب
  $total = 0;

  // المرور على عناصر العربة المخزنة في session
  foreach ($cart as $key => $c) {

    // إذا المفتاح يبدأ بـ v_ فهذا يعني منتج مع لون (Variant)
    if (strpos($key, 'v_') === 0) {

      // جلب بيانات المنتج مع اللون من جدول variants + products
      $stmt = $pdo->prepare("
        SELECT 
          p.name,
          p.price,
          pv.color
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        WHERE pv.id = ?
      ");

      // تنفيذ الاستعلام باستخدام variant_id
      $stmt->execute([(int)$c['variant_id']]);

      // جلب صف واحد
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

    } else {

      // منتج بدون لون: جلبه من جدول products فقط
      $stmt = $pdo->prepare("
        SELECT 
          name,
          price,
          NULL AS color
        FROM products
        WHERE id = ?
      ");

      // تنفيذ الاستعلام باستخدام product_id
      $stmt->execute([(int)$c['product_id']]);

      // جلب صف واحد
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // إذا لم نجد المنتج في قاعدة البيانات، تجاهل هذا العنصر
    if (!$row) continue;

    // الكمية المطلوبة
    $qty = (int)$c['qty'];

    // إجمالي هذا السطر = الكمية × سعر الوحدة
    $line = $qty * (float)$row['price'];

    // إضافة للسعر الإجمالي
    $total += $line;

    // تجهيز بيانات هذا العنصر لعرضه لاحقًا
    $items[] = [
      'name'  => $row['name'],
      'color' => $row['color'],
      'qty'   => $qty,
      'price'=> $row['price'],
      'line' => $line
    ];
  }

  // إذا بعد المعالجة ما طلع عناصر (مثلاً منتجات محذوفة)
  if (!$items) {
    echo '<p>🛒 عربتك فارغة.</p>';
  } else {

    // طباعة بداية الجدول
    echo '
      <table class="table">
        <thead>
          <tr>
            <th>المنتج</th>
            <th>اللون</th>
            <th>الكمية</th>
            <th>السعر</th>
            <th>الإجمالي</th>
          </tr>
        </thead>
        <tbody>
    ';

    // طباعة صف لكل عنصر
    foreach ($items as $it) {
      echo '
        <tr>
          <td>'.htmlspecialchars($it['name']).'</td>
          <td>'.($it['color'] ? htmlspecialchars($it['color']) : '—').'</td>
          <td>'.$it['qty'].'</td>
          <td>'.number_format($it['price'], 2).'</td>
          <td>'.number_format($it['line'], 2).'</td>
        </tr>
      ';
    }

    // إغلاق الجدول
    echo '
        </tbody>
      </table>
    ';

    // عرض إجمالي العربة
    echo '
      <p class="total">
        الإجمالي: <strong>'.number_format($total, 2).'</strong>
      </p>
    ';

    // زر الانتقال لصفحة checkout
    echo '
      <p>
        <a class="btn primary" href="checkout.php">
          تأكيد الطلب (الدفع عند الاستلام)
        </a>
      </p>
    ';

    // ملاحظة صغيرة للمستخدم
    echo '
      <p class="muted">
        <em>سيتم الدفع عند الاستلام أو من نقطة الاستلام.</em>
      </p>
    ';
  }
}
?>
</main>

</body>
</html>

