<?php
// تحميل إعدادات المشروع
require_once __DIR__ . '/config.php';

// تشغيل session إذا لم تكن شغالة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل ملفات قاعدة البيانات + الصلاحيات
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// التحقق: لازم المستخدم يكون مسجل دخول
if (!is_logged_in()) {
  // رسالة مؤقتة (Flash) لعرضها للمستخدم
  $_SESSION['flash'] = 'يرجى تسجيل الدخول لإضافة منتجات إلى العربة.';

  // تحويل المستخدم لتسجيل الدخول
  header('Location: user_login.php');
  exit;
}

// التحقق: لازم البيانات الأساسية تكون موجودة (product_id و qty)
if (
  empty($_POST['product_id']) ||
  empty($_POST['qty'])
) {
  // إذا البيانات ناقصة رجّعه للرئيسية
  header('Location: index.php');
  exit;
}

// قراءة رقم المنتج وتحويله لعدد صحيح
$product_id = (int)$_POST['product_id'];

// قراءة رقم اللون (إن وُجد) وإلا null
$variant_id = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;

// قراءة الكمية والتأكد أنها لا تقل عن 1
$qty        = max(1, (int)$_POST['qty']);

// إنشاء اتصال قاعدة البيانات
$pdo = db();

// إذا كان هناك لون محدد (variant)
if ($variant_id) {

  // تجهيز استعلام: جلب المنتج + مخزون اللون المحدد
  $stmt = $pdo->prepare(
    "SELECT 
        pv.id   AS variant_id,
        pv.stock AS stock,
        p.id    AS product_id,
        p.name,
        p.price
     FROM product_variants pv
     JOIN products p ON p.id = pv.product_id
     WHERE pv.id = :vid AND p.id = :pid
     LIMIT 1"
  );

  // تنفيذ الاستعلام مع تمرير variant_id و product_id
  $stmt->execute([
    ':vid' => $variant_id,
    ':pid' => $product_id
  ]);

} else {

  // تجهيز استعلام: جلب المنتج بدون ألوان (مخزون عام)
  $stmt = $pdo->prepare(
    "SELECT 
        id AS product_id,
        name,
        price,
        stock
     FROM products
     WHERE id = :pid
     LIMIT 1"
  );

  // تنفيذ الاستعلام مع تمرير product_id
  $stmt->execute([
    ':pid' => $product_id
  ]);
}

// جلب نتيجة الاستعلام (صف واحد)
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// إذا المنتج غير موجود (أو اللون لا يطابق المنتج)
if (!$row) {
  $_SESSION['flash'] = 'المنتج غير موجود.';
  header('Location: index.php');
  exit;
}

// التحقق من المخزون: إذا الكمية المطلوبة أكبر من المخزون
if ($row['stock'] < $qty) {
  $_SESSION['flash'] = 'الكمية المطلوبة غير متوفرة.';
  header('Location: product_details.php?id=' . $product_id);
  exit;
}

// إذا العربة غير موجودة في session، أنشئها كمصفوفة
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// تحديد مفتاح العنصر داخل العربة:
// - منتج مع لون: v_ID
// - منتج بدون لون: p_ID
$key = $variant_id > 0
  ? 'v_' . $variant_id   // منتج مع لون
  : 'p_' . $product_id;  // منتج بدون لون

// إذا العنصر غير موجود في العربة، أنشئه أولًا
if (!isset($_SESSION['cart'][$key])) {
  $_SESSION['cart'][$key] = [
    'product_id' => $product_id,
    'variant_id' => $variant_id,
    'qty' => 0
  ];
}

// زيادة الكمية داخل العربة
$_SESSION['cart'][$key]['qty'] += $qty;

// تحويل المستخدم لصفحة العربة
header('Location: cart.php');
exit;

