<?php
// تحميل الإعدادات العامة
require_once __DIR__ . '/config.php';

// تشغيل الجلسة إذا لم تكن مفعلة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحميل ملف التحقق من الصلاحيات
require_once __DIR__ . '/auth.php';

// السماح فقط للأدمن بدخول الصفحة
require_admin();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- عنوان الصفحة -->
  <title>لوحة تحكم المسؤول - Modern MiniStore</title>

  <!-- ملف التنسيق العام -->
  <link rel="stylesheet" href="assets/style.css" />

  <!-- ملف JavaScript العام -->
  <script src="assets/app.js" defer></script>

  <style>

  /* =========================
     تنسيق الصفحة العامة
     ========================= */
  body {
    /* خلفية متدرجة بنفسجية */
    background: linear-gradient(145deg, #5b2abf, #7d4dff);

    /* لون النص أبيض */
    color: #fff;

    /* خط الصفحة */
    font-family: "Cairo", sans-serif;

    /* أقل ارتفاع = طول الشاشة */
    min-height: 100vh;

    /* استخدام Flexbox */
    display: flex;

    /* ترتيب العناصر عمودي */
    flex-direction: column;

    /* توسيط المحتوى أفقيًا */
    align-items: center;
  }

  /* =========================
     الهيدر (رأس الصفحة)
     ========================= */
  header {
    /* عرض كامل الصفحة */
    width: 100%;

    /* خلفية شفافة */
    background: rgba(255,255,255,0.08);

    /* تأثير زجاجي (ضبابية الخلفية) */
    backdrop-filter: blur(6px);

    /* مسافات داخلية */
    padding: 1rem 2rem;

    /* ترتيب العناصر باستخدام flex */
    display: flex;

    /* توزيع العناصر يمين ويسار */
    justify-content: space-between;

    /* محاذاة عمودية */
    align-items: center;

    /* خط سفلي خفيف */
    border-bottom: 1px solid rgba(255,255,255,0.2);
  }

  /* =========================
     عنوان الهيدر
     ========================= */
  header h1 {
    /* ترتيب الشعار مع النص */
    display: flex;

    /* محاذاة عمودية */
    align-items: center;

    /* مسافة بين الشعار والنص */
    gap: 10px;

    /* حجم الخط */
    font-size: 1.5rem;
  }

  /* =========================
     صورة الشعار
     ========================= */
  header img {
    /* عرض الصورة */
    width: 36px;

    /* ارتفاع الصورة */
    height: 36px;

    /* زيادة الإضاءة */
    filter: brightness(1.3);
  }

  /* =========================
     المحتوى الرئيسي
     ========================= */
  main {
    /* يأخذ المساحة المتبقية */
    flex: 1;

    /* استخدام flex */
    display: flex;

    /* ترتيب عمودي */
    flex-direction: column;

    /* توسيط عمودي */
    justify-content: center;

    /* توسيط أفقي */
    align-items: center;

    /* مسافة بين العناصر */
    gap: 2rem;
  }

  /* =========================
     حاوية الأزرار
     ========================= */
  .btns {
    /* ترتيب الأزرار أفقي */
    display: flex;

    /* السماح بالتفاف الأزرار */
    flex-wrap: wrap;

    /* مسافة بين الأزرار */
    gap: 1rem;

    /* توسيط الأزرار */
    justify-content: center;
  }

  /* =========================
     زر عام
     ========================= */
  .btn {
    /* خلفية شفافة */
    background: rgba(255,255,255,0.15);

    /* حجم الزر */
    padding: 1rem 2rem;

    /* تدوير الزوايا */
    border-radius: 10px;

    /* حجم الخط */
    font-size: 1.2rem;

    /* لون النص */
    color: #fff;

    /* إزالة الإطار */
    border: none;

    /* شكل المؤشر */
    cursor: pointer;

    /* حركة ناعمة */
    transition: all 0.3s ease;

    /* إزالة خط الرابط */
    text-decoration: none;
  }

  /* =========================
     تأثير عند المرور على الزر
     ========================= */
  .btn:hover {
    /* تفتيح الخلفية */
    background: rgba(255,255,255,0.3);

    /* تكبير بسيط */
    transform: scale(1.05);
  }

  /* =========================
     زر تسجيل الخروج
     ========================= */
  .logout-btn {
    /* خلفية شفافة */
    background: rgba(255,255,255,0.15);

    /* بدون إطار */
    border: none;

    /* لون النص */
    color: #fff;

    /* حجم الزر */
    padding: 0.6rem 1.2rem;

    /* تدوير الزوايا */
    border-radius: 8px;

    /* شكل المؤشر */
    cursor: pointer;

    /* حركة ناعمة */
    transition: background 0.3s;
  }

  /* =========================
     hover لزر الخروج
     ========================= */
  .logout-btn:hover {
    /* تفتيح الخلفية */
    background: rgba(255,255,255,0.3);
  }

</style>

</head>

<body>

  <!-- رأس لوحة التحكم -->
  <header>
    <h1>
      <img src="assets/logo.png" alt="Logo" />
      <span>🛍️ Modern Store — مرحبًا بك يا أدمن!</span>
    </h1>

    <!-- زر تسجيل الخروج -->
    <a href="admin_logout.php" class="logout-btn">تسجيل الخروج 🔓</a>
  </header>

  <!-- المحتوى الرئيسي -->
  <main>
    <div class="btns">

      <!-- زر إدارة المنتجات -->
      <a href="admin_products.php" class="btn">
        إدارة المنتجات 🛒
      </a>

      <!-- زر إدارة الطلبات -->
      <a href="admin_orders.php" class="btn">
        إدارة الطلبات 📦
      </a>

    </div>
  </main>

</body>
</html>
