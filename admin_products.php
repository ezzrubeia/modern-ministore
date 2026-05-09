<?php
// تحميل ملف الإعدادات العامة (BASE_URL - timezone ...)
require_once __DIR__ . '/config.php';

// بدء الجلسة إذا لم تكن مفعلة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/db.php';

// تحميل ملف الصلاحيات (is_admin / require_admin)
require_once __DIR__ . '/auth.php';

// منع الدخول إلا للمسؤول
require_admin();

// الاتصال بقاعدة البيانات
$pdo = db();

// متغيرات رسائل النجاح والخطأ
$msg = '';
$err = '';

/* ================= ADD PRODUCT ================= */
// التحقق إذا تم إرسال فورم إضافة منتج
if (isset($_POST['add_product'])) {

  // اسم المنتج
  $name = trim($_POST['name']);

  // وصف المنتج (اختياري)
  $description = trim($_POST['description'] ?? '');

  // سعر المنتج
  $price = (float)$_POST['price'];

  // المخزون العام
  $stock = (int)$_POST['stock'];

  // اسم اللون
  $color_name = trim($_POST['color_name']);

  // كمية اللون
  $color_qty = (int)$_POST['color_qty'];

  // مسار الصورة الافتراضي
  $imagePath = '';

  // التحقق إذا تم رفع صورة
  if (!empty($_FILES['image']['name'])) {

    // مجلد الصور
    $uploadDir = __DIR__ . '/uploads/';

    // إنشاء المجلد إذا غير موجود
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // إنشاء اسم فريد للصورة
    $filename = time() . '_' . basename($_FILES['image']['name']);

    // المسار النهائي
    $target = $uploadDir . $filename;

    // نقل الصورة من المؤقت
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      $imagePath = 'uploads/' . $filename;
    }
  }

  // إدخال المنتج في جدول products
  $stmt = $pdo->prepare("
    INSERT INTO products (name, description, price, stock, image_url)
    VALUES (:n, :d, :p, :s, :i)
  ");

  $stmt->execute([
    ':n' => $name,
    ':d' => $description,
    ':p' => $price,
    ':s' => $stock,
    ':i' => $imagePath
  ]);

  // جلب ID المنتج الجديد
  $product_id = (int)$pdo->lastInsertId();

  // إضافة اللون إذا تم إدخاله
  if ($color_name && $color_qty > 0) {
    $pdo->prepare(
      "INSERT INTO product_variants (product_id, color, stock)
       VALUES (?, ?, ?)"
    )->execute([$product_id, $color_name, $color_qty]);
  }

  // رسالة نجاح
  $msg = '✅ تمت إضافة المنتج مع اللون بنجاح';
}

/* ================= EDIT PRODUCT ================= */
// التحقق إذا تم إرسال فورم تعديل منتج
if (isset($_POST['edit_product'])) {

  // ID المنتج
  $id = (int)$_POST['id'];

  // البيانات الجديدة
  $name = trim($_POST['name']);
  $description = trim($_POST['description'] ?? '');
  $price = (float)$_POST['price'];
  $stock = (int)$_POST['stock'];

  // الصورة القديمة
  $imagePath = $_POST['old_image'] ?? '';

  // إذا تم رفع صورة جديدة
  if (!empty($_FILES['image']['name'])) {

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = time() . '_' . basename($_FILES['image']['name']);
    $target = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      $imagePath = 'uploads/' . $filename;
    }
  }

  // تحديث بيانات المنتج
  $pdo->prepare("
    UPDATE products
    SET name=?, description=?, price=?, stock=?, image_url=?
    WHERE id=?
  ")->execute([$name, $description, $price, $stock, $imagePath, $id]);

  $msg = '✏️ تم تعديل المنتج';
}

/* ================= DELETE PRODUCT ================= */
// حذف المنتج
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  // حذف المنتج (والألوان تحذف تلقائيًا)
  $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$id]);

  $msg = '🗑️ تم حذف المنتج';
}

/* ================= VARIANTS ================= */
// إضافة لون
if (isset($_POST['add_variant'])) {
  $pdo->prepare(
    "INSERT INTO product_variants (product_id, color, stock)
     VALUES (?, ?, ?)"
  )->execute([
    (int)$_POST['product_id'],
    trim($_POST['color']),
    (int)$_POST['stock']
  ]);
  $msg = '🎨 تم إضافة لون';
}

// تعديل لون
if (isset($_POST['edit_variant'])) {
  $pdo->prepare(
    "UPDATE product_variants SET color=?, stock=? WHERE id=?"
  )->execute([
    trim($_POST['color']),
    (int)$_POST['stock'],
    (int)$_POST['variant_id']
  ]);
  $msg = '🎨 تم تعديل اللون';
}

// حذف لون
if (isset($_POST['delete_variant'])) {
  $pdo->prepare("DELETE FROM product_variants WHERE id=?")
      ->execute([(int)$_POST['variant_id']]);
  $msg = '🗑️ تم حذف اللون';
}

/* ================= DATA ================= */
// جلب جميع المنتجات
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")
                ->fetchAll(PDO::FETCH_ASSOC);

// دالة لجلب ألوان منتج
function getVariants($pdo, $productId) {
  $s = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ?");
  $s->execute([$productId]);
  return $s->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>إدارة المنتجات</title>
<link rel="stylesheet" href="assets/style.css">
<style>
/* ================================
   تنسيقات صفحة إدارة المنتجات (ADMIN)
================================ */

/* الحاوية العامة للصفحة */
.admin-wrap {
  max-width: 1200px; /* أقصى عرض للواجهة */
  margin: auto;      /* توسيط المحتوى أفقياً */
}

/* كرت المنتج (الصندوق الرئيسي لكل منتج) */
.product-card {
  background: rgba(255,255,255,0.08); /* خلفية شفافة */
  border: 1px solid rgba(255,255,255,0.15); /* إطار خفيف */
  border-radius: 16px; /* زوايا دائرية */
  padding: 18px;       /* مسافة داخلية */
  margin-bottom: 25px; /* مسافة أسفل كل كرت */
}

/* الجزء العلوي للكرت (الصورة + بيانات المنتج) */
.product-top {
  display: grid; /* استخدام Grid */
  grid-template-columns: 140px 1fr; /* عمود للصورة + عمود للمحتوى */
  gap: 18px; /* مسافة بين الأعمدة */
  align-items: start; /* محاذاة العناصر من الأعلى */
}

/* حاوية صورة المنتج */
.product-img {
  width: 140px;  /* عرض ثابت */
  height: 140px; /* ارتفاع ثابت */
  border-radius: 12px; /* تدوير الزوايا */
  overflow: hidden; /* قص أي جزء زائد من الصورة */
  background: rgba(0,0,0,0.3); /* خلفية داكنة */
  border: 1px solid rgba(255,255,255,0.2); /* إطار للصورة */
}

/* الصورة داخل الحاوية */
.product-img img {
  width: 100%;  /* تملىء العرض */
  height: 100%; /* تملىء الارتفاع */
  object-fit: cover; /* قص الصورة بدون تشويه */
}

/* فورم تعديل المنتج */
.product-form {
  display: grid; /* Grid layout */
  grid-template-columns: repeat(4, 1fr); /* 4 أعمدة متساوية */
  gap: 10px; /* مسافة بين الحقول */
}

/* حقول الإدخال والنص */
.product-form input,
.product-form textarea {
  width: 100%; /* عرض كامل */
  padding: 8px; /* مسافة داخلية */
  border-radius: 8px; /* زوايا دائرية */
  border: none; /* بدون إطار افتراضي */
}

/* textarea يأخذ صف كامل */
.product-form textarea {
  grid-column: span 4; /* يمتد على 4 أعمدة */
  resize: vertical;   /* يسمح بتغيير الارتفاع فقط */
}

/* حاوية أزرار تعديل / حذف المنتج */
.product-actions {
  grid-column: span 4; /* صف كامل */
  display: flex;       /* Flexbox */
  gap: 10px;           /* مسافة بين الأزرار */
}

/* تنسيق الأزرار والروابط */
.product-actions button,
.product-actions a {
  padding: 8px 14px; /* حجم الزر */
  border-radius: 8px; /* تدوير */
  border: none;       /* بدون إطار */
  cursor: pointer;    /* شكل المؤشر */
  font-weight: bold;  /* خط عريض */
  text-decoration: none; /* إزالة خط الروابط */
}

/* زر تعديل المنتج */
.btn-edit {
  background:#a78bfa; /* بنفسجي فاتح */
  color:#000;         /* نص أسود */
}

/* زر حذف المنتج */
.btn-delete {
  background:#ef4444; /* أحمر */
  color:#fff;         /* نص أبيض */
}

/* ================================
   تنسيقات الألوان (VARIANTS)
================================ */

/* صندوق الألوان */
.variants-box {
  margin-top: 18px; /* مسافة فوق */
  padding-top: 12px; /* مسافة داخلية */
  border-top: 1px dashed rgba(255,255,255,0.3); /* خط فاصل */
}

/* عنوان قسم الألوان */
.variants-title {
  margin-bottom: 10px;
  font-weight: bold;
  opacity: .9;
}

/* صف اللون الواحد */
.variant-row {
  display: grid;
  grid-template-columns: 2fr 1fr auto auto; /* لون - كمية - أزرار */
  gap: 8px;
  margin-bottom: 8px;
}

/* حقول اللون */
.variant-row input {
  padding: 6px;
  border-radius: 6px;
  border: none;
}

/* أزرار تعديل / حذف اللون */
.variant-row button {
  padding: 6px 10px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

/* زر تعديل اللون */
.variant-edit {
  background:#22c55e; /* أخضر */
}

/* زر حذف اللون */
.variant-delete {
  background:#ef4444; /* أحمر */
  color:#fff;
}

/* فورم إضافة لون جديد */
.add-variant {
  margin-top: 10px;
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 8px;
}

/* زر إضافة اللون */
.add-variant button {
  background:#38bdf8; /* أزرق */
  font-weight:bold;
}

/* ================================
   تنسيق الحقول (Dark Theme)
================================ */

/* جميع الحقول */
input,
textarea,
select {
  background: rgba(255,255,255,0.08); /* خلفية داكنة */
  color: #fff; /* لون النص */
  border: 1px solid rgba(255,255,255,0.25); /* إطار خفيف */
  border-radius: 10px; /* تدوير */
  padding: 10px;
  outline: none; /* إزالة الإطار الأزرق */
  transition: 0.25s ease; /* انتقال ناعم */
}

/* لون placeholder */
input::placeholder,
textarea::placeholder {
  color: rgba(255,255,255,0.6);
}

/* عند التركيز على الحقل */
input:focus,
textarea:focus,
select:focus {
  background: rgba(255,255,255,0.12);
  border-color: #a78bfa;
  box-shadow: 0 0 0 2px rgba(167,139,250,0.35);
}

/* حقل رفع الملفات */
input[type="file"] {
  background: transparent;
  border: none;
  color: #fff;
}

/* إعادة تعريف Grid للـ variant-row (للمحاذاة) */
.variant-row {
  display: grid;
  grid-template-columns: 2fr 1fr auto auto;
  gap: 10px;
  align-items: center;
}

/* إعادة تعريف فورم إضافة اللون */
.add-variant {
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 10px;
  margin-top: 10px;
}
</style>

</head>
<body>

<!-- رابط الرجوع إلى لوحة تحكم الأدمن -->
<a href="admin_dashboard.php">⬅️ رجوع</a>

<!-- عنوان الصفحة -->
<h1>🛍️ إدارة المنتجات</h1>

<!-- في حال وجود رسالة (نجاح / حذف / تعديل) يتم عرضها -->
<?php if($msg): ?>
  <p><?= htmlspecialchars($msg) ?></p>
<?php endif; ?>

<!-- عنوان قسم إضافة منتج -->
<h3>➕ إضافة منتج</h3>

<!-- فورم إضافة منتج جديد -->
<form method="post" enctype="multipart/form-data">
  <!-- اسم المنتج -->
  <input name="name" placeholder="اسم المنتج" required>

  <!-- وصف المنتج -->
  <textarea name="description" placeholder="وصف المنتج" required></textarea>

  <!-- سعر المنتج (رقم عشري) -->
  <input type="number" step="0.01" name="price" placeholder="السعر" required>

  <!-- المخزون العام للمنتج -->
  <input type="number" name="stock" placeholder="مخزون عام">

  <!-- اسم لون واحد (اختياري) -->
  <input name="color_name" placeholder="لون (أحمر)">

  <!-- كمية اللون -->
  <input type="number" name="color_qty" placeholder="كمية اللون">

  <!-- صورة المنتج -->
  <input type="file" name="image" required>

  <!-- زر إرسال الفورم لإضافة المنتج -->
  <button name="add_product">➕ إضافة</button>
</form>

<hr>

<!-- حلقة تمر على جميع المنتجات -->
<?php foreach($products as $p): ?>

<div class="product-card">
  <!-- كرت منتج واحد -->

  <div class="product-top">
    <!-- الجزء العلوي (الصورة + بيانات المنتج) -->

    <!-- صورة المنتج -->
    <div class="product-img">
      <?php if ($p['image_url']): ?>
        <!-- إذا كان للمنتج صورة -->
        <img src="<?= htmlspecialchars($p['image_url']) ?>">
      <?php else: ?>
        <!-- في حال لا توجد صورة -->
        <div style="padding:20px;text-align:center;">لا صورة</div>
      <?php endif; ?>
    </div>

    <!-- فورم تعديل بيانات المنتج -->
    <form method="post" enctype="multipart/form-data" class="product-form">

      <!-- ID المنتج (مخفي) -->
      <input type="hidden" name="id" value="<?= $p['id'] ?>">

      <!-- مسار الصورة القديمة -->
      <input type="hidden" name="old_image" value="<?= $p['image_url'] ?>">

      <!-- اسم المنتج -->
      <input name="name" value="<?= htmlspecialchars($p['name']) ?>">

      <!-- سعر المنتج -->
      <input type="number" step="0.01" name="price" value="<?= $p['price'] ?>">

      <!-- مخزون المنتج -->
      <input type="number" name="stock" value="<?= $p['stock'] ?>">

      <!-- رفع صورة جديدة (اختياري) -->
      <input type="file" name="image">

      <!-- وصف المنتج -->
      <textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea>

      <!-- أزرار التعديل والحذف -->
      <div class="product-actions">

        <!-- زر تعديل المنتج -->
        <button name="edit_product" class="btn-edit">✏️ تعديل</button>

        <!-- رابط حذف المنتج مع تأكيد -->
        <a href="?delete=<?= $p['id'] ?>"
           class="btn-delete"
           onclick="return confirm('حذف المنتج؟')">
           🗑️ حذف
        </a>

      </div>
    </form>

  </div>

  <!-- قسم الألوان الخاصة بالمنتج -->
  <div class="variants-box">

    <!-- عنوان قسم الألوان -->
    <div class="variants-title">🎨 الألوان</div>

    <!-- عرض جميع الألوان التابعة للمنتج -->
    <?php foreach (getVariants($pdo, $p['id']) as $v): ?>

      <!-- فورم تعديل / حذف لون -->
      <form method="post" class="variant-row">

        <!-- ID اللون -->
        <input type="hidden" name="variant_id" value="<?= $v['id'] ?>">

        <!-- اسم اللون -->
        <input name="color" value="<?= htmlspecialchars($v['color']) ?>">

        <!-- مخزون اللون -->
        <input type="number" name="stock" value="<?= $v['stock'] ?>">

        <!-- زر تعديل اللون -->
        <button name="edit_variant" class="variant-edit">✔</button>

        <!-- زر حذف اللون -->
        <button name="delete_variant" class="variant-delete">✖</button>
      </form>

    <?php endforeach; ?>

    <!-- فورم إضافة لون جديد -->
    <form method="post" class="add-variant">

      <!-- ID المنتج -->
      <input type="hidden" name="product_id" value="<?= $p['id'] ?>">

      <!-- اسم اللون الجديد -->
      <input name="color" placeholder="لون جديد">

      <!-- كمية اللون -->
      <input type="number" name="stock" placeholder="كمية">

      <!-- زر الإضافة -->
      <button name="add_variant">➕</button>
    </form>

  </div>
</div>



<hr>

<?php endforeach; ?>

</body>
</html>
