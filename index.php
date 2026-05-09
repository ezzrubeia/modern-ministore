<?php
// تحميل الإعدادات العامة (مثل BASE_URL)
require_once __DIR__ . '/config.php';

// تشغيل الـ session إذا لم تكن شغالة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل ملف قاعدة البيانات + ملف الصلاحيات
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>🛍️ Modern MiniStore</title>

  <!-- ملف CSS الأساسي -->
  <link rel="stylesheet" href="assets/style.css" />

  <script>
    // متغيرات عامة للـ JavaScript (هل المستخدم أدمن؟ هل مسجل دخول؟)
    //window : كائن عام في المتصفح.
    //IS_ADMIN : متغير عالمي أنت بتخزنه على window.

    window.IS_ADMIN = <?php echo is_admin() ? 'true' : 'false'; ?>;
    window.IS_LOGGED = <?php echo !empty($_SESSION['user']['id']) ? 'true' : 'false'; ?>;
  </script>

  <!-- ملف JavaScript العام (يعمل بعد تحميل الصفحة بسبب defer) -->
  <script src="assets/app.js" defer></script>

  <style>
  /* =========================
     إعدادات الصفحة العامة
     ========================= */
  body {
    /* خلفية متدرجة بنفسجية تعطي طابع عصري */
    background: linear-gradient(135deg, #4b0082, #7a2ff7);

    /* لون النص أبيض */
    color: #fff;

    /* الخط المستخدم في الموقع */
    font-family: "Cairo", sans-serif;

    /* إزالة الهوامش الافتراضية */
    margin: 0;

    /* إزالة الحشوة الافتراضية */
    padding: 0;

    /* أقل ارتفاع = ارتفاع الشاشة كاملة */
    min-height: 100vh;
  }

  /* =========================
     الهيدر (الشريط العلوي)
     ========================= */
  header.container {
    /* استخدام flex لترتيب العناصر */
    display: flex;

    /* توزيع العناصر: واحد يمين وواحد يسار */
    justify-content: space-between;

    /* توسيط العناصر عموديًا */
    align-items: center;

    /* مسافات داخلية */
    padding: 20px 30px;

    /* خلفية شفافة */
    background: rgba(255,255,255,0.1);

    /* تأثير ضبابي خلف الهيدر */
    backdrop-filter: blur(6px);

    /* خط سفلي خفيف */
    border-bottom: 1px solid rgba(255,255,255,0.15);
  }

  /* عنوان المتجر داخل الهيدر */
  header h1 {
    /* حجم الخط */
    font-size: 1.6rem;

    /* إزالة الهامش الافتراضي */
    margin: 0;

    /* استخدام flex لمحاذاة الأيقونة مع النص */
    display: flex;
    align-items: center;

    /* مسافة بين النص والإيموجي */
    gap: 8px;
  }

  /* =========================
     روابط التنقل (القائمة)
     ========================= */
  nav a {
    /* لون النص */
    color: white;

    /* إزالة الخط السفلي */
    text-decoration: none;

    /* مسافة بين الروابط */
    margin: 0 8px;

    /* حشوة داخل الرابط */
    padding: 6px 12px;

    /* تدوير الزوايا */
    border-radius: 8px;

    /* خلفية شفافة */
    background: rgba(255,255,255,0.1);

    /* حركة ناعمة عند hover */
    transition: background 0.3s;
  }

  /* شكل الرابط عند المرور عليه */
  nav a:hover {
    /* زيادة وضوح الخلفية */
    background: rgba(255,255,255,0.25);
  }

  /* =========================
     منطقة عرض المنتجات
     ========================= */
  main.container {
    /* استخدام Grid */
    display: grid;

    /* أعمدة مرنة حسب حجم الشاشة */
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));

    /* مسافة بين الكروت */
    gap: 20px;

    /* حشوة داخلية */
    padding: 40px;
  }

  /* =========================
     كرت المنتج
     ========================= */
  .card {
    /* خلفية شفافة */
    background: rgba(255,255,255,0.1);

    /* زوايا دائرية */
    border-radius: 16px;

    /* توسيط النص */
    text-align: center;

    /* حشوة داخلية */
    padding: 16px;

    /* حركة ناعمة */
    transition: transform 0.3s ease, background 0.3s ease;
  }

  /* تأثير عند المرور على الكرت */
  .card:hover {
    /* رفع الكرت للأعلى */
    transform: translateY(-5px);

    /* زيادة وضوح الخلفية */
    background: rgba(255,255,255,0.2);
  }

  /* =========================
     صورة المنتج
     ========================= */
  .card img {
    /* عرض كامل */
    width: 100%;

    /* زوايا دائرية */
    border-radius: 12px;

    /* مسافة أسفل الصورة */
    margin-bottom: 10px;

    /* قص الصورة بشكل مناسب */
    object-fit: cover;

    /* ارتفاع ثابت */
    height: 180px;
  }

  /* =========================
     الأزرار
     ========================= */
  .btn {
    /* لون الخلفية */
    background: #fff;

    /* لون النص */
    color: #4b0082;

    /* إزالة الإطار */
    border: none;

    /* زوايا دائرية */
    border-radius: 10px;

    /* حشوة داخلية */
    padding: 8px 14px;

    /* تغيير شكل المؤشر */
    cursor: pointer;

    /* جعل النص عريض */
    font-weight: bold;

    /* حركة ناعمة */
    transition: 0.3s;
  }

  /* تأثير عند المرور على الزر */
  .btn:hover {
    /* تغيير لون الخلفية */
    background: #e2d4ff;

    /* تكبير بسيط */
    transform: scale(1.05);
  }

  /* =========================
     الفوتر (أسفل الصفحة)
     ========================= */
  footer {
    /* توسيط النص */
    text-align: center;

    /* حشوة داخلية */
    padding: 20px;

    /* لون نص خافت */
    color: #ccc;

    /* خلفية شفافة */
    background: rgba(255,255,255,0.08);

    /* خط علوي */
    border-top: 1px solid rgba(255,255,255,0.15);
  }
</style>

</head>

<body>
  <header class="container">
    <h1>Modern MiniStore 🛍️</h1>

    <nav>
      <!-- روابط عامة -->
      <a href="index.php">المنتجات</a>
      <a href="cart.php">العربة</a>
      <a href="my_orders.php">طلباتي</a>

      <!-- روابط تظهر فقط للأدمن -->
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

  <!-- مكان عرض المنتجات (سيتم تعبئته بالجافاسكربت) -->
  <main class="container" id="products-list">
    <p style="grid-column:1/-1;text-align:center;color:#eee;">
      جارٍ تحميل المنتجات...
    </p>
  </main>

  <footer>
    <p>© 2025 Modern MiniStore. جميع الحقوق محفوظة.</p>
  </footer>

  <script>
    // متغير يساعدنا نعرف هل المستخدم مسجّل دخول
    const loggedIn = <?php echo is_logged_in() ? 'true' : 'false'; ?>;

    
    // دالة تحميل المنتجات من API وعرضها
    //async : دالة غير متزامنة (تسمح باستخدام await).
    //'products-list' : نفس id الموجود على main.
     //listEl : صار يمثل عنصر main.
    async function loadProducts() {
      const listEl = document.getElementById('products-list');

      try {
        // طلب المنتجات من API
        const res = await fetch('api/products.php');
        if (!res.ok) throw new Error('فشل تحميل المنتجات');

        // تحويل الرد من JSON إلى Array
        const products = await res.json();

        // إذا ما في منتجات
        if (!products.length) {
          listEl.innerHTML = `
            <p style="grid-column:1/-1;text-align:center;">
              🚫 لا توجد منتجات حالياً.
            </p>
          `;
          return;
        }

        // بناء كروت المنتجات وعرضها
        //innerHTML : استبدال محتوى العنصر HTML.
        //products.map(...) : يحول كل منتج إلى نص HTML.
        //P المنتج الحالي
        listEl.innerHTML = products.map(p => `
          <div class="card">
            <img src="${p.image_url || 'assets/no_image.png'}" alt="${p.name}">
            <h3>${p.name}</h3>
            <p>USD ${p.price.toFixed(2)}</p>

            ${loggedIn
              ? `<a class="btn" href="product_details.php?id=<?= (int)$p['id'] ?>">عرض التفاصيل</a>`
              : `<a class="btn" href="user_login.php">سجّل الدخول للشراء</a>`
            }
          </div>
          
        `).join('');//لأن map يرجع Array نصوص.
         //join يجمعهم في String واحد بدون فواصل.

      } catch (e) {
        // إذا فشل التحميل لأي سبب
        listEl.innerHTML = `
          <p style="grid-column:1/-1;text-align:center;">
            ⚠️ تعذر تحميل المنتجات.
          </p>
        `;
      }
    }

    // تشغيل تحميل المنتجات عند فتح الصفحة
    loadProducts();
  </script>
</body>
</html>
