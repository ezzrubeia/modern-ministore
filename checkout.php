<?php
// إظهار جميع أخطاء PHP (مفيد أثناء التطوير)
//ini_set : تغيير إعدادات PHP وقت التشغيل.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);//تحديد مستوى الخطورة (كل الاخطاءو التحذيرات)

// تشغيل session
session_start();

// تحميل ملفات الإعدادات والاتصال والصلاحيات
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// التأكد أن المستخدم مسجل دخول (وإلا تحويله لصفحة login)
require_login();

// قراءة العربة من الـ session
$cart = $_SESSION['cart'] ?? [];

// إنشاء اتصال قاعدة البيانات
$pdo  = db();

// رابط إرسال الطلب للـ API (الدفع عند الاستلام)
$actionUrl = rtrim(BASE_URL, '/') . '/api/cod_place_order.php';

// مصفوفة العناصر التي ستُعرض في ملخص الطلب
$items = [];

// إذا العربة تحتوي على عناصر
if (!empty($cart)) {

  // مصفوفات لتجميع IDs
  $variantIds = [];
  $productIds = [];

  // المرور على عناصر العربة
  foreach ($cart as $key => $row) {
    // إذا المفتاح يبدأ بـ v_ فهو منتج مع لون
    if (str_starts_with($key, 'v_')) {
      $variantIds[] = (int)$row['variant_id'];
    } else {
      // منتج بدون لون
      $productIds[] = (int)$row['product_id'];
    }
  }

  $items = [];

  /* ===== منتجات مع ألوان ===== */
  if ($variantIds) {

    //(arr->string)   إنشاء placeholders بعدد الألوان
    $in = implode(',', array_fill(0, count($variantIds), '?'));

    // جلب المنتجات مع ألوانها
    $stmt = $pdo->prepare(
      "SELECT
        pv.id   AS variant_id,
        p.id    AS product_id,
        p.name,
        p.price,
        pv.color
       FROM product_variants pv
       JOIN products p ON p.id = pv.product_id
       WHERE pv.id IN ($in)"
    );

    // تنفيذ الاستعلام
    $stmt->execute($variantIds);

    // دمج النتائج مع عناصر الطلب
    $items = array_merge($items, $stmt->fetchAll(PDO::FETCH_ASSOC));
  }

  /* ===== منتجات بدون ألوان ===== */
  if ($productIds) {

    // إنشاء placeholders بعدد المنتجات
    $in = implode(',', array_fill(0, count($productIds), '?'));

    // جلب المنتجات بدون ألوان
    $stmt = $pdo->prepare(
      "SELECT
        id AS product_id,
        name,
        price,
        NULL AS variant_id,
        NULL AS color
       FROM products
       WHERE id IN ($in)"
    );

    // تنفيذ الاستعلام
    $stmt->execute($productIds);

    // دمج النتائج
    $items = array_merge($items, $stmt->fetchAll(PDO::FETCH_ASSOC));
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>تأكيد الطلب — الدفع عند الاستلام</title>

  <!-- ملف CSS العام -->
  <link rel="stylesheet" href="assets/style.css" />

  <style>
  /* =========================
     تخطيط الصفحة الرئيسية
     ========================= */
  main.container {
    /* استخدام Grid لتقسيم الصفحة */
    display: grid;

    /* عمود للفورم (60%) وعمود لملخص الطلب (40%) */
    grid-template-columns: 60% 40%;

    /* مسافة بين العمودين */
    gap: 24px;

    /* مسافة من الأعلى */
    margin-top: 30px;
  }

  /* =========================
     تصميم الشاشات الصغيرة
     ========================= */
  @media (max-width: 900px) {
    main.container {
      /* جعل التخطيط عمود واحد في الجوال */
      grid-template-columns: 1fr;
    }
  }

  /* =========================
     كرت (Card) عام
     ========================= */
  .card {
    /* لون خلفية الكرت (من المتغيرات) */
    background: var(--card);

    /* إطار خفيف */
    border: 1px solid rgba(255,255,255,.08);

    /* تدوير الزوايا */
    border-radius: var(--radius);

    /* مسافة داخلية */
    padding: 20px;

    /* ظل خفيف */
    box-shadow: var(--shadow);
  }

  /* =========================
     عناوين الأقسام
     ========================= */
  h3 {
    /* إزالة المسافة العلوية الافتراضية */
    margin-top: 0;

    /* لون النص (من المتغيرات) */
    color: var(--text);
  }

  /* =========================
     شبكة نموذج الإدخال
     ========================= */
  .form-grid {
    /* استخدام Grid داخل الفورم */
    display: grid;

    /* مسافة بين الحقول */
    gap: 12px;
  }

  /* =========================
     الحقول (input / select / textarea)
     ========================= */
  input, select, textarea {
    /* عرض كامل */
    width: 100%;

    /* حشوة داخلية */
    padding: 12px 14px;

    /* تدوير الزوايا */
    border-radius: 10px;

    /* إطار خفيف */
    border: 1px solid rgba(255,255,255,.18);

    /* خلفية شفافة */
    background: rgba(255,255,255,.08);

    /* لون النص */
    color: #fff;
  }

  /* =========================
     حقل الملاحظات
     ========================= */
  textarea {
    /* السماح بتغيير الارتفاع فقط */
    resize: vertical;

    /* أقل ارتفاع */
    min-height: 70px;
  }

  /* =========================
     زر تأكيد الطلب
     ========================= */
  .btn.primary {
    /* عرض كامل */
    width: 100%;

    /* خلفية متدرجة */
    background: linear-gradient(135deg,#9b5cff,#b26fff);

    /* لون النص */
    color:#120926;

    /* جعل النص عريض */
    font-weight:700;

    /* حشوة داخلية */
    padding:12px 16px;

    /* إزالة الإطار */
    border:none;

    /* تدوير الزوايا */
    border-radius:10px;

    /* تغيير شكل المؤشر */
    cursor:pointer;

    /* حركة ناعمة */
    transition:.2s;
  }

  /* =========================
     تأثير عند المرور على الزر
     ========================= */
  .btn.primary:hover {
    /* تكبير بسيط */
    transform: scale(1.03);

    /* زيادة الإضاءة */
    filter: brightness(1.15);
  }

  /* =========================
     قائمة ملخص الطلب
     ========================= */
  aside.card ul {
    /* إزالة النقاط */
    list-style:none;

    /* إزالة الحشوة */
    padding:0;

    /* إزالة الهوامش */
    margin:0;
  }

  /* =========================
     عنصر واحد في ملخص الطلب
     ========================= */
  aside.card li {
    /* ترتيب أفقي */
    display:flex;

    /* توزيع الاسم والسعر */
    justify-content:space-between;

    /* مسافة عمودية */
    margin:6px 0;
  }

  /* =========================
     الخط الفاصل في الملخص
     ========================= */
  aside.card hr {
    /* خط شفاف */
    border: 1px solid rgba(255,255,255,.1);

    /* مسافة فوق وتحت */
    margin:10px 0;
  }
</style>

</head>

<body>

<!-- الهيدر -->
<header class="container">
  <h1>🧾 تأكيد الطلب</h1>

  <nav>
    <a href="index.php">المنتجات</a>
    <a href="cart.php">العربة</a>
    <a href="my_orders.php">طلباتي</a>
    <span class="muted">مرحباً، <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
    <a href="user_logout.php">خروج</a>
  </nav>
</header>

<main class="container">

<!-- فورم بيانات العميل -->
<section class="card">
  <h3>معلومات الاستلام / التوصيل</h3>

  <?php if (!$cart): ?>
    <p class="muted">عربتك فارغة.</p>
  <?php else: ?>
    <form action="<?= htmlspecialchars($actionUrl) ?>" method="post" class="form-grid">

      <label>الاسم الكامل
        <input type="text" name="customer_name" required>
      </label>

      <label>رقم الهاتف
        <input type="text" name="customer_phone" required>
      </label>

      <label>طريقة الاستلام
        <select name="delivery_method" required>
          <option value="delivery">توصيل</option>
          <option value="pickup">استلام من نقطة</option>
        </select>
      </label>

      <label>العنوان
        <input type="text" name="address">
      </label>

      <label>نقطة الاستلام
        <input type="text" name="pickup_location">
      </label>

      <label>ملاحظات
        <textarea name="notes"></textarea>
      </label>

      <button class="btn primary">تأكيد الطلب</button>
    </form>
  <?php endif; ?>
</section>

<!-- ملخص الطلب -->
 <!-- aside:محتوى جانبي أو مكمل للمحتوى الرئيسي-->
<aside class="card">
  <h3>ملخص الطلب</h3>

  <?php if (!$items): ?>
    <p class="muted">لا توجد عناصر.</p>
  <?php else: ?>

    <ul>
      <?php
      $total = 0;

      foreach ($items as $it):
        $key = $it['variant_id']
          ? 'v_' . $it['variant_id']
          : 'p_' . $it['product_id'];

        $qty = (int)($cart[$key]['qty'] ?? 0);
        $line = $qty * (float)$it['price'];
        $total += $line;
      ?>
        <li>
          <span>
            <?= htmlspecialchars($it['name']) ?>
            <?= $it['color'] ? ' - ' . htmlspecialchars($it['color']) : '' ?>
            × <?= $qty ?>
          </span>
          <strong><?= number_format($line, 2) ?> USD</strong>
        </li>
      <?php endforeach; ?>
    </ul>

    <hr>
    <p class="row">
      <span>الإجمالي</span>
      <strong><?= number_format($total, 2) ?> USD</strong>
    </p>

    <p class="muted">الدفع عند الاستلام (COD)</p>

  <?php endif; ?>
</aside>

</main>

<script>
  // الانتظار حتى يتم تحميل الصفحة بالكامل (HTML جاهز)
  window.addEventListener('DOMContentLoaded', () => {

    // جلب جميع العناصر التي تحمل الكلاس "card"
    document.querySelectorAll('.card').forEach(c => {

      // إضافة كلاس CSS جديد لعمل تأثير الحركة (animation)
      c.classList.add('reveal-in');

    });
  });
</script>


</body>
</html>
checkout.php
