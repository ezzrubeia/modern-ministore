<?php

// تحميل ملف الإعدادات العامة (مثل BASE_URL)
require_once __DIR__ . '/config.php';

// تشغيل الـ session إذا لم تكن مفعّلة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تحميل ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/db.php';

// إنشاء اتصال بقاعدة البيانات
$pdo = db();

// متغير لتخزين رسالة الخطأ
$err = '';

// متغير لتخزين رسالة النجاح
$msg = '';

// إذا تم إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // قراءة القيم من النموذج مع إزالة الفراغات
  $name    = trim($_POST['name'] ?? '');
  $email   = trim($_POST['email'] ?? '');
  $pass    = trim($_POST['password'] ?? '');
  $confirm = trim($_POST['confirm'] ?? '');

  // التحقق من إدخال جميع الحقول
  if (!$name || !$email || !$pass) {

    // في حال وجود حقل فارغ
    $err = 'الرجاء إدخال جميع الحقول.';

  // التحقق من تطابق كلمتي المرور
  } elseif ($pass !== $confirm) {

    $err = 'كلمتا المرور غير متطابقتين.';

  } else {

    // فحص إذا كان البريد الإلكتروني مستخدم مسبقًا
    $check = $pdo->prepare('SELECT id FROM users WHERE email = :e LIMIT 1');
    $check->execute([':e' => $email]);

    // إذا وُجد مستخدم بنفس البريد
    if ($check->fetch()) {

      $err = 'هذا البريد مستخدم بالفعل.';

    } else {

      // تشفير كلمة المرور قبل تخزينها (bcrypt افتراضيًا)
      $hash = password_hash($pass, PASSWORD_DEFAULT);

      // تجهيز استعلام إدخال مستخدم جديد
      $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash) 
         VALUES (:n, :e, :h)'
      );

      // تنفيذ الاستعلام مع تمرير القيم
      $stmt->execute([
        ':n' => $name,
        ':e' => $email,
        ':h' => $hash
      ]);

      // رسالة نجاح بعد إنشاء الحساب
      $msg = 'تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
    }
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

  <!-- ترميز الصفحة لدعم العربية -->
  <meta charset="UTF-8" />

  <!-- إعداد العرض للأجهزة المختلفة -->
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>إنشاء حساب</title>

  <!-- ملف التنسيق -->
  <link rel="stylesheet" href="assets/style.css" />

  <!-- ملف الجافاسكربت -->
  <script src="assets/app.js" defer></script>

</head>
<body>

  <main class="container" style="max-width:520px;margin-top:48px;">

    <div class="card" data-reveal>

      <h2>إنشاء حساب جديد</h2>

      <!-- عرض رسالة الخطأ إذا وُجدت -->
      <?php if ($err): ?>
        <p class="muted" style="color:#ef4444;">
          <?php echo htmlspecialchars($err); ?>
        </p>

      <!-- عرض رسالة النجاح إذا وُجدت -->
      <?php elseif ($msg): ?>
        <p class="muted" style="color:#22d3ee;">
          <?php echo htmlspecialchars($msg); ?>
        </p>
      <?php endif; ?>

      <!-- نموذج إنشاء حساب -->
      <form method="post" class="form-grid">

        <label>
          الاسم الكامل
          <input name="name" type="text" required />
        </label>

        <label>
          البريد الإلكتروني
          <input name="email" type="email" required />
        </label>

        <label>
          كلمة المرور
          <input name="password" type="password" required />
        </label>

        <label>
          تأكيد كلمة المرور
          <input name="confirm" type="password" required />
        </label>

        <button class="btn primary" type="submit">
          إنشاء حساب
        </button>

        <p class="muted">
          لديك حساب بالفعل؟
          <a href="user_login.php">تسجيل الدخول</a>
        </p>

      </form>

    </div>

  </main>

</body>
</html>
