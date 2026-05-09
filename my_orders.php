<?php
// تحميل ملف الإعدادات العامة
require_once __DIR__ . '/config.php';

// تشغيل session إذا لم تكن مفعلة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل الاتصال بقاعدة البيانات
require_once __DIR__ . '/db.php';

// تحميل دوال التحقق من تسجيل الدخول
require_once __DIR__ . '/auth.php';

// منع الدخول إذا لم يكن المستخدم مسجل
require_login();

// إنشاء اتصال PDO
$pdo = db();

// مصفوفة لتخزين الطلبات
$orders = [];

// مصفوفة لتخزين عناصر كل طلب
$itemsByOrder = [];

// جلب ID المستخدم الحالي
$uid = current_user_id();

// تجهيز استعلام لجلب طلبات المستخدم
$stmt = $pdo->prepare(
  "SELECT * FROM orders 
   WHERE user_id = :uid 
   ORDER BY id ASC"
);

// تنفيذ الاستعلام
$stmt->execute([':uid' => $uid]);

// جلب جميع الطلبات
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// إذا وجد طلبات
if ($orders) {

  // استخراج IDs الطلبات
  $ids = array_column($orders, 'id');

  // إنشاء IN (?, ?, ?)
  $in = implode(',', array_fill(0, count($ids), '?'));

  // جلب عناصر الطلبات
  $stmt2 = $pdo->prepare(
    "SELECT * FROM order_items 
     WHERE order_id IN ($in)"
  );

  // تنفيذ الاستعلام
  $stmt2->execute($ids);

  // تجميع العناصر حسب order_id
  foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $it) {
    $itemsByOrder[$it['order_id']][] = $it;
  }
}

// دالة ترجع كلاس CSS واسم الحالة
function order_status_meta(string $status): array {

  // إزالة الفراغات
  $status = trim($status);

  return match ($status) {

    // حالات الطلب
    'قيد التأكيد' => ['badge-pending', 'قيد التأكيد'],
    'قيد التنفيذ' => ['badge-processing', 'قيد التنفيذ'],
    'مكتمل'       => ['badge-completed', 'مكتمل'],
    'ملغى'        => ['badge-cancelled', 'ملغى'],

    // أي حالة غير معروفة
    default => ['badge-neutral', $status],
  };
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>طلباتي</title>

  <!-- ملف CSS العام -->
  <link rel="stylesheet" href="assets/style.css" />

  <style>
  /* =========================
     الحاوية الرئيسية للطلبات
     ========================= */
  main {
    /* استخدام Grid لترتيب كروت الطلبات */
    display: grid;

    /* مسافة بين كل كرت طلب */
    gap: 22px;

    /* توسيط المحتوى + مسافة من الأعلى */
    margin: 40px auto;

    /* أقصى عرض للصفحة */
    max-width: 900px;
  }

  /* =========================
     كرت الطلب الواحد
     ========================= */
  .order-card {
    /* خلفية متدرجة داكنة */
    background: linear-gradient(
      145deg,
      rgba(50, 20, 85, 0.9),
      rgba(70, 25, 110, 0.8)
    );

    /* إطار خفيف حول الكرت */
    border: 1px solid rgba(255,255,255,0.12);

    /* تدوير الزوايا */
    border-radius: 18px;

    /* مسافة داخلية */
    padding: 22px;

    /* لون النص */
    color: #fff;

    /* ظل لإعطاء عمق */
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);

    /* حركة ناعمة عند hover */
    transition: 
      transform 0.3s ease,
      box-shadow 0.3s ease;
  }

  /* =========================
     تأثير عند المرور على الكرت
     ========================= */
  .order-card:hover {
    /* رفع الكرت للأعلى */
    transform: translateY(-4px);

    /* زيادة الظل */
    box-shadow: 0 12px 30px rgba(150, 90, 255, 0.25);
  }

  /* =========================
     رأس كرت الطلب
     ========================= */
  .order-header {
    /* استخدام flex */
    display: flex;

    /* توزيع العناصر يمين ويسار */
    justify-content: space-between;

    /* محاذاة عمودية */
    align-items: center;

    /* مسافة أسفل الرأس */
    margin-bottom: 8px;
  }

  /* عنوان الطلب */
  .order-header h3 {
    /* إزالة الهوامش الافتراضية */
    margin: 0;

    /* حجم الخط */
    font-size: 18px;

    /* لون النص */
    color: #d6c9e6;
  }

  /* =========================
     شارة حالة الطلب
     ========================= */
  .badge {
    /* عرض مرن */
    display: inline-flex;

    /* محاذاة عمودية */
    align-items: center;

    /* مسافة بين النقطة والنص */
    gap: 8px;

    /* مسافات داخلية */
    padding: 6px 12px;

    /* شكل دائري */
    border-radius: 999px;

    /* حجم النص */
    font-size: 13px;

    /* جعل النص عريض */
    font-weight: 700;
  }

  /* =========================
     ألوان حالات الطلب
     ========================= */

  /* قيد التأكيد */
  .badge-pending {
    background: rgba(250,204,21,.15);
    color: #fde68a;
  }

  /* مكتمل */
  .badge-completed {
    background: rgba(16,185,129,.15);
    color: #a7f3d0;
  }

  /* ملغى */
  .badge-cancelled {
    background: rgba(239,68,68,.15);
    color: #fecaca;
  }

  /* حالة افتراضية */
  .badge-neutral {
    background: rgba(250,204,21,.15);
    color: #fde68a;
  }

  /* =========================
     النقطة الدائرية قبل الحالة
     ========================= */
  .badge::before {
    /* عنصر وهمي */
    content: "";

    /* عرض النقطة */
    width: 8px;

    /* ارتفاع النقطة */
    height: 8px;

    /* جعلها دائرية */
    border-radius: 50%;

    /* نفس لون النص */
    background: currentColor;
  }

  /* =========================
     قائمة عناصر الطلب
     ========================= */
  ul.order-items {
    /* إزالة النقاط */
    list-style: none;

    /* إزالة الحشوة */
    padding: 0;

    /* مسافات علوية وسفلية */
    margin: 8px 0 12px;
  }

  /* عنصر واحد داخل الطلب */
  ul.order-items li {
    /* ترتيب الاسم والسعر */
    display: flex;

    /* توزيع الاسم والسعر */
    justify-content: space-between;

    /* مسافة عمودية */
    padding: 6px 0;
  }

  /* =========================
     سطر الإجمالي
     ========================= */
  .total-line {
    /* ترتيب أفقي */
    display: flex;

    /* توزيع النص والسعر */
    justify-content: space-between;

    /* جعل النص عريض */
    font-weight: bold;
  }

  /* =========================
     معلومات التوصيل
     ========================= */
  .delivery-info {
    /* مسافة من الأعلى */
    margin-top: 6px;

    /* حجم خط أصغر */
    font-size: 14px;

    /* لون أفتح */
    color: #cbbbee;
  }

  /* =========================
     نص ثانوي
     ========================= */
  .muted {
    /* لون خافت */
    color: #bdaedb;
  }
</style>

</head>
<body>

<!-- الهيدر -->
<header class="container">
  <h1>🧾 طلباتي</h1>

  <nav>
    <a href="index.php">المنتجات</a>
    <a href="cart.php">العربة</a>
    <a href="my_orders.php">طلباتي</a>

    <?php if (is_admin()): ?>
      <a href="orders.php">الطلبات (مسؤول)</a>
      <a href="admin_logout.php">خروج (مسؤول)</a>
    <?php endif; ?>

    <?php if (is_logged_in()): ?>
      <span class="muted">
        مرحباً، <?= htmlspecialchars($_SESSION['user']['name']) ?>
      </span>
      <a href="user_logout.php">خروج</a>
    <?php endif; ?>
  </nav>
</header>

<main>

<?php if (!$orders): ?>
  <!-- في حال لا توجد طلبات -->
  <p style="text-align:center;">لا توجد طلبات حالياً.</p>

<?php else: ?>

<?php
// عدّاد لترقيم الطلبات
$i = 1;

// المرور على جميع الطلبات
foreach ($orders as $o):
?>

<?php
// جلب كلاس CSS واسم الحالة حسب حالة الطلب
[$stClass, $stLabel] = order_status_meta($o['status'] ?? '');
?>

<!-- كرت الطلب -->
<section class="order-card reveal-in">

  <!-- رأس الكرت -->
  <div class="order-header">
    <!-- رقم الطلب -->
    <h3>طلب رقم <?= $i++; ?></h3>

    <!-- تاريخ الطلب -->
    <span class="muted">
      <?= htmlspecialchars($o['created_at']); ?>
    </span>
  </div>

  <!-- شارة حالة الطلب -->
  <div class="badge <?= $stClass; ?>">
    <?= htmlspecialchars($stLabel); ?>
  </div>

  <!-- عناصر الطلب -->
  <ul class="order-items">
    <?php foreach ($itemsByOrder[$o['id']] ?? [] as $it): ?>
      <li>
        <!-- اسم المنتج والكمية -->
        <span>
          <?= htmlspecialchars($it['name']); ?> × <?= (int)$it['qty']; ?>
        </span>

        <!-- سعر العنصر -->
        <strong>
          <?= number_format($it['unit_price'] * $it['qty'], 2); ?> USD
        </strong>
      </li>
    <?php endforeach; ?>
  </ul>

  <!-- إجمالي الطلب -->
  <div class="total-line">
    <span>الإجمالي:</span>
    <strong>
      <?= number_format($o['amount'], 2); ?> USD
    </strong>
  </div>

  <!-- طريقة الاستلام -->
  <div class="delivery-info">
    طريقة الاستلام:
    <strong>
      <?= $o['delivery_method'] === 'pickup' ? 'استلام' : 'توصيل'; ?>
    </strong>
  </div>

</section>

<?php endforeach; ?>
<?php endif; ?>
</main>

<script>
  // إضافة أنيميشن دخول متدرج لكروت الطلبات
  window.addEventListener('DOMContentLoaded', () => {

    // جلب جميع عناصر كروت الطلبات
    document.querySelectorAll('.order-card').forEach((c, i) => {

      // تأخير إضافة الأنيميشن لكل كرت
      setTimeout(() => {

        // إضافة كلاس الأنيميشن للكرت
        c.classList.add('reveal-in');

      }, 120 * (i + 1)); // كل كرت يتأخر عن السابق
    });
  });
</script>


</body>
</html>
