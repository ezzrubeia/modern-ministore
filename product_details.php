<?php
// تحميل الإعدادات العامة
require_once __DIR__ . '/config.php';

// تشغيل session إذا لم تكن شغالة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل ملف قاعدة البيانات
require_once __DIR__ . '/db.php';

// إنشاء اتصال بقاعدة البيانات
$pdo = db();

/* ================== جلب ID المنتج ================== */

// قراءة ID المنتج من الرابط وتحويله لرقم صحيح
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// إذا كان ID غير صالح، ارجع للصفحة الرئيسية
if ($id <= 0) {
  header('Location: index.php');
  exit;
}

/* ================== جلب المنتج ================== */

// تجهيز استعلام لجلب المنتج
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");

// تنفيذ الاستعلام مع ربط ID
$stmt->execute([':id' => $id]);

// جلب بيانات المنتج
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// إذا المنتج غير موجود
if (!$product) {
  header('Location: index.php');
  exit;
}


// تجهيز استعلام لجلب ألوان المنتج
$st2 = $pdo->prepare("
  SELECT id, color, stock
  FROM product_variants
  WHERE product_id = :pid
  ORDER BY color ASC
");

// تنفيذ الاستعلام
$st2->execute([':pid' => $id]);

// جلب جميع الألوان
$variants = $st2->fetchAll(PDO::FETCH_ASSOC);

// هل المستخدم مسجل دخول؟
$is_logged = !empty($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>تفاصيل المنتج</title>

  <!-- ملف CSS العام -->
  <link rel="stylesheet" href="assets/style.css" />

  <style>
  /* =========================
     إعدادات الصفحة العامة
     ========================= */
  body{
    /* خلفية متدرجة بنفسجية */
    background: linear-gradient(145deg, #5b2abf, #7d4dff);

    /* لون النص أبيض */
    color:#fff;

    /* الخط المستخدم */
    font-family:"Cairo", sans-serif;

    /* أقل ارتفاع = طول الشاشة */
    min-height:100vh;

    /* مسافة داخلية حول الصفحة */
    padding:20px;
  }

  /* =========================
     حاوية المحتوى الرئيسية
     ========================= */
  .wrap{
    /* أقصى عرض للمحتوى */
    max-width:1000px;

    /* توسيط الحاوية بالصفحة */
    margin:0 auto;
  }

  /* =========================
     كرت تفاصيل المنتج
     ========================= */
  .cardx{
    /* خلفية شفافة */
    background:rgba(255,255,255,0.12);

    /* إطار خفيف */
    border:1px solid rgba(255,255,255,0.2);

    /* تدوير الزوايا */
    border-radius:14px;

    /* مسافة داخلية */
    padding:18px;
  }

  /* =========================
     شبكة الصورة + التفاصيل
     ========================= */
  .grid{
    /* استخدام CSS Grid */
    display:grid;

    /* عمود للصورة + عمود للتفاصيل */
    grid-template-columns: 360px 1fr;

    /* مسافة بين الأعمدة */
    gap:18px;

    /* محاذاة العناصر من الأعلى */
    align-items:start;
  }

  /* =========================
     حاوية صورة المنتج
     ========================= */
  .img{
    /* عرض كامل */
    width:100%;

    /* تدوير الزوايا */
    border-radius:12px;

    /* إخفاء أي زيادة من الصورة */
    overflow:hidden;

    /* خلفية داكنة */
    background:rgba(0,0,0,0.2);

    /* إطار خفيف */
    border:1px solid rgba(255,255,255,0.2);
  }

  /* =========================
     صورة المنتج نفسها
     ========================= */
  .img img{
    /* عرض كامل */
    width:100%;

    /* إزالة المسافات الافتراضية */
    display:block;
  }

  /* =========================
     زر عام
     ========================= */
  .btnx{
    /* عرض inline */
    display:inline-block;

    /* حشوة داخلية */
    padding:10px 16px;

    /* تدوير الزوايا */
    border-radius:10px;

    /* إزالة الخط السفلي */
    text-decoration:none;

    /* إزالة الإطار */
    border:none;

    /* شكل المؤشر */
    cursor:pointer;

    /* جعل النص عريض */
    font-weight:700;
  }

  /* =========================
     زر أساسي (بنفسجي)
     ========================= */
  .primary{
    /* لون الخلفية */
    background:#a78bfa;

    /* لون النص */
    color:#fff;
  }

  /* =========================
     زر أخضر (رجوع)
     ========================= */
  .green{
    /* لون الخلفية */
    background:#22c55e;

    /* لون النص */
    color:#fff;
  }

  /* =========================
     قائمة اختيار (Select)
     ========================= */
  select{
    /* عرض كامل */
    width:100%;

    /* حشوة داخلية */
    padding:10px;

    /* تدوير الزوايا */
    border-radius:10px;

    /* إزالة الإطار */
    border:none;

    /* مسافة من الأعلى */
    margin-top:8px;
  }

  /* =========================
     نص ثانوي (أخف)
     ========================= */
  .muted{
    /* تقليل وضوح النص */
    opacity:.9;
  }

  /* =========================
     تصميم الشاشات الصغيرة
     ========================= */
  @media (max-width: 900px){
    /* جعل الشبكة عمود واحد في الجوال */
    .grid{
      grid-template-columns:1fr;
    }
  }
</style>

</head>

<body>

<div class="wrap">

  <!-- زر الرجوع -->
  <a class="btnx green" href="index.php">⬅️ رجوع للمنتجات</a>

  <div class="cardx" style="margin-top:14px;">
    <div class="grid">

      <!-- صورة المنتج -->
      <div class="img">
        <?php if (!empty($product['image_url'])): ?>
          <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="product">
        <?php else: ?>
          <div style="padding:40px;text-align:center;">لا توجد صورة</div>
        <?php endif; ?>
      </div>

      <!-- تفاصيل المنتج -->
      <div>

        <!-- اسم المنتج -->
        <h2><?= htmlspecialchars($product['name']) ?></h2>

        <!-- السعر -->
        <p class="muted" style="font-size:18px;">
          السعر: <b><?= number_format((float)$product['price'], 2) ?> USD</b>
        </p>

        <!-- الكمية العامة -->
        <p class="muted">
          الكمية العامة: <b><?= (int)$product['stock'] ?></b>
        </p>

        <!-- وصف المنتج -->
        <?php if (!empty($product['description'])): ?>
          <p class="muted" style="margin-top:12px;line-height:1.7;">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
          </p>
        <?php endif; ?>

        <hr style="border-color:rgba(255,255,255,0.2);margin:14px 0;">

        <!-- فورم إضافة للعربة -->
        <form method="post" action="cart_add.php">

          <!-- ID المنتج -->
          <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

          <?php if (!empty($variants)): ?>
            <!-- المنتج يحتوي على ألوان -->
            <label>اختر اللون (وكمية اللون المتاحة):</label>

            <select name="variant_id" required>
              <option value="" disabled selected>اختر لون</option>

              <?php foreach ($variants as $v): ?>
                <option value="<?= (int)$v['id'] ?>" <?= ((int)$v['stock'] <= 0 ? 'disabled' : '') ?>>
                  <?= htmlspecialchars($v['color']) ?> — المتاح: <?= (int)$v['stock'] ?>
                  <?= ((int)$v['stock'] <= 0 ? ' (غير متوفر)' : '') ?>
                </option>
              <?php endforeach; ?>
            </select>

          <?php else: ?>
            <!-- المنتج بدون ألوان -->
            <input type="hidden" name="variant_id" value="0">
            <p class="muted">هذا المنتج لا يحتوي على ألوان.</p>
          <?php endif; ?>

          <!-- اختيار الكمية -->
          <label style="display:block;margin-top:12px;">الكمية المطلوبة:</label>
          <input type="number" name="qty" min="1" value="1" required
                 style="padding:10px;border-radius:10px;border:none;width:140px;">

          <!-- زر الإضافة -->
          <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
            <?php if ($is_logged): ?>
              <button class="btnx primary" type="submit">🛒 إضافة للعربة</button>
            <?php else: ?>
              <a class="btnx primary"
                 href="user_login.php?next=<?= urlencode('product_details.php?id=' . $product['id']) ?>">
                🔒 تسجيل الدخول للشراء
              </a>
            <?php endif; ?>
          </div>

        </form>

      </div>

    </div>
  </div>

</div>

</body>
</html>
