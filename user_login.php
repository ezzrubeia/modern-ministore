<?php

// تحميل ملف الإعدادات (BASE_URL + إعدادات عامة)
require_once __DIR__ . '/config.php';

// تشغيل session إذا لم تكن مفعّلة
if (session_status() === PHP_SESSION_NONE) session_start();

// تحميل ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/db.php';

// الحصول على اتصال PDO
$pdo = db();

// متغير لتخزين رسالة الخطأ (إن وُجدت)
$err = '';

// رابط الصفحة المطلوب الرجوع لها بعد تسجيل الدخول (إن وُجد)
$next = $_GET['next'] ?? '';

// إذا كان الطلب من نوع POST (تم إرسال الفورم)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // جلب البريد الإلكتروني من الفورم مع حذف الفراغات
  $email = trim($_POST['email'] ?? '');

  // جلب كلمة المرور من الفورم مع حذف الفراغات
  $pass = trim($_POST['password'] ?? '');

  // تجهيز استعلام لجلب المستخدم حسب البريد الإلكتروني
  $stmt = $pdo->prepare(
    'SELECT id, name, email, password_hash, role FROM users WHERE email = :e LIMIT 1'
  );

  // تنفيذ الاستعلام مع تمرير البريد الإلكتروني
  $stmt->execute([':e' => $email]);

  // جلب بيانات المستخدم (إن وُجد)
  $u = $stmt->fetch();

  // إذا لم يتم العثور على المستخدم أو كلمة المرور غير صحيحة
  if (!$u || !password_verify($pass, $u['password_hash'])) {

    // رسالة خطأ عامة (بدون تحديد الخطأ لأسباب أمنية)
    $err = 'بيانات الدخول غير صحيحة.';

  } else {

    // تخزين بيانات المستخدم في session
    $_SESSION['user'] = [
      'id'    => (int)$u['id'],   // رقم المستخدم
      'name'  => $u['name'],      // الاسم
      'email' => $u['email'],     // البريد الإلكتروني
      'role'  => $u['role']       // الدور (user / admin)
    ];

    // إذا كان المستخدم أدمن، تحويله للوحة تحكم الأدمن
    if ($u['role'] === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    }

    // تحويل المستخدم:
    // - إما للصفحة المطلوبة سابقًا (next)
    // - أو للصفحة الرئيسية index.php
    header('Location: ' . ($next ?: 'index.php'));
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <!-- ترميز الصفحة -->
  <meta charset="UTF-8" />

  <!-- جعل الصفحة متجاوبة مع الموبايل -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- عنوان الصفحة -->
  <title>تسجيل الدخول</title>

  <!-- ملف التنسيقات -->
  <link rel="stylesheet" href="assets/style.css" />

  <!-- ملف الجافاسكربت (يعمل بعد تحميل الصفحة) -->
  <script src="assets/app.js" defer></script>
</head>
<body>

  <!-- المحتوى الرئيسي -->
  <main class="container" style="max-width:520px;margin-top:48px;">

    <!-- كرت تسجيل الدخول -->
    <div class="card" data-reveal>

      <h2>تسجيل الدخول</h2>

      <!-- عرض رسالة الخطأ إن وُجدت -->
      <?php if ($err): ?>
        <p class="muted" style="color:#ef4444;">
          <?php echo htmlspecialchars($err); ?>
        </p>
      <?php endif; ?>

      <!-- نموذج تسجيل الدخول -->
      <form method="post" class="form-grid">

        <!-- حقل البريد الإلكتروني -->
        <label>البريد الإلكتروني
          <input name="email" type="email" required />
        </label>

        <!-- حقل كلمة المرور -->
        <label>كلمة المرور
          <input name="password" type="password" required />
        </label>

        <!-- زر الإرسال -->
        <button class="btn primary" type="submit">دخول</button>
        
        <!-- رابط إنشاء حساب -->
        <p class="muted">
          ليس لديك حساب؟ <a href="user_register.php">إنشاء حساب</a>
        </p>

      </form>
    </div>
  </main>
</body>
</html>
