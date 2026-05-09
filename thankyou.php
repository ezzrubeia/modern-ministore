<?php
// عرض جميع أخطاء PHP (للتطوير)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// تحميل الإعدادات العامة
require_once __DIR__ . '/config.php';

// تحميل ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/db.php';

// تشغيل session إذا لم تكن شغّالة
if (session_status() === PHP_SESSION_NONE) session_start();

// دالة مساعدة لحماية النصوص من XSS
function h($s){
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// قراءة رقم الطلب من الرابط
$order_id = (int)($_GET['order_id'] ?? 0);

// إنشاء اتصال قاعدة البيانات
$pdo = db();

// تجهيز استعلام لجلب الطلب
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id");

// تنفيذ الاستعلام
$stmt->execute([
    ':id' => $order_id
]);

// جلب بيانات الطلب
$o = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>تم استلام طلبك</title>

  <!-- ملف التنسيقات العام -->
  <link rel="stylesheet" href="assets/style.css" />

 <style>
  /* =========================
     إعدادات الصفحة العامة
     ========================= */
  body {
    /* خلفية متدرجة بنفسجية */
    background: linear-gradient(135deg, #4b0082, #7a2ff7);

    /* لون النص أبيض */
    color: #fff;

    /* الخط المستخدم */
    font-family: "Cairo", sans-serif;

    /* إزالة الهوامش */
    margin: 0;

    /* إزالة الحشوة */
    padding: 0;

    /* ارتفاع الصفحة كامل الشاشة */
    min-height: 100vh;

    /* استخدام Flex لتوسيط المحتوى */
    display: flex;

    /* ترتيب عمودي */
    flex-direction: column;

    /* توسيط عمودي */
    justify-content: center;

    /* توسيط أفقي */
    align-items: center;

    /* توسيط النص */
    text-align: center;
  }

  /* =========================
     صندوق رسالة الشكر
     ========================= */
  .thankyou-box {
    /* خلفية شفافة */
    background: rgba(255,255,255,0.08);

    /* مسافات داخلية */
    padding: 40px 50px;

    /* تدوير الزوايا */
    border-radius: 20px;

    /* ظل للصندوق */
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);

    /* أقصى عرض */
    max-width: 480px;

    /* حركة ظهور */
    animation: fadeIn 1s ease forwards;
  }

  /* =========================
     حركة الظهور للصندوق
     ========================= */
  @keyframes fadeIn {
    from {
      /* شفاف */
      opacity: 0;

      /* تصغير */
      transform: scale(0.92);
    }
    to {
      /* ظاهر */
      opacity: 1;

      /* الحجم الطبيعي */
      transform: scale(1);
    }
  }

  /* =========================
     دائرة علامة الصح
     ========================= */
  .checkmark {
    /* العرض */
    width: 80px;

    /* الارتفاع */
    height: 80px;

    /* شكل دائري */
    border-radius: 50%;

    /* توسيط المحتوى داخلها */
    display: grid;
    place-items: center;

    /* خلفية خضراء متدرجة */
    background: linear-gradient(135deg, #22c55e, #4ade80);

    /* توسيط أفقي + مسافة أسفل */
    margin: 0 auto 20px;

    /* لتفعيل pseudo-element */
    position: relative;

    /* حركة pop */
    animation: pop 0.6s ease forwards;
  }

  /* =========================
     علامة الصح داخل الدائرة
     ========================= */
  .checkmark::before {
    /* النص */
    content: "✓";

    /* حجم الرمز */
    font-size: 40px;

    /* جعل الرمز عريض */
    font-weight: bold;

    /* لون الرمز */
    color: #fff;

    /* حركة الظهور */
    animation: appear 0.6s ease forwards;
  }

  /* =========================
     حركة تكبير الدائرة
     ========================= */
 @keyframes pop {
  /* بداية الحركة */
  0% {
    /* العنصر صغير جدًا */
    transform: scale(0);

    /* العنصر شفاف */
    opacity: 0;
  }

  /* منتصف الحركة */
  60% {
    /* تكبير زائد لإحساس القفزة */
    transform: scale(1.2);

    /* العنصر ظاهر */
    opacity: 1;
  }

  /* نهاية الحركة */
  100% {
    /* الحجم الطبيعي */
    transform: scale(1);
  }
}

  }

  /* =========================
     حركة ظهور علامة الصح
     ========================= */
  @keyframes appear {
    from {
      opacity: 0;
      transform: scale(0.6);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }

  /* =========================
     عنوان الصفحة
     ========================= */
  h3 {
    /* حجم الخط */
    font-size: 1.6rem;

    /* مسافة أسفل */
    margin-bottom: 10px;

    /* لون بنفسجي فاتح */
    color: #f0e8ff;
  }

  /* =========================
     نص ثانوي
     ========================= */
  .muted {
    /* لون أخف */
    color: #d1c4f9;

    /* حجم خط أصغر */
    font-size: 0.95rem;

    /* مسافة أعلى */
    margin-top: 8px;
  }

  /* =========================
     زر الرجوع للمتجر
     ========================= */
  a.btn {
    /* عرض inline */
    display: inline-block;

    /* خلفية متدرجة */
    background: linear-gradient(135deg, #9b5cff, #b26fff);

    /* لون النص */
    color: #120926;

    /* جعل النص عريض */
    font-weight: 700;

    /* إزالة الخط السفلي */
    text-decoration: none;

    /* مسافات داخلية */
    padding: 10px 18px;

    /* تدوير الزوايا */
    border-radius: 10px;

    /* حركة ناعمة */
    transition: all 0.25s ease;

    /* مسافة أعلى */
    margin-top: 20px;
  }

  /* =========================
     تأثير hover على الزر
     ========================= */
  a.btn:hover {
    /* تكبير بسيط */
    transform: scale(1.05);

    /* زيادة الإضاءة */
    filter: brightness(1.2);
  }
</style>

</head>

<body>
  <div class="thankyou-box">

    <!-- علامة الصح -->
    <div class="checkmark"></div>

    <?php if(!$o): ?>
      <!-- في حال لم يتم العثور على الطلب -->
      <h3>❌ لم يتم العثور على الطلب.</h3>
      <p>
        <a class="btn" href="index.php">العودة للمتجر</a>
      </p>

    <?php else: ?>
      <!-- في حال نجاح الطلب -->
      <h3>✅ تم استلام طلبك بنجاح</h3>

      <p>
        رقم الطلب:
        <strong>#<?= (int)$o['id'] ?></strong>
      </p>

      <p>
        الحالة الحالية:
        <strong><?= h($o['status']) ?></strong>
      </p>

      <p>
        المبلغ:
        <strong>
          <?= number_format((float)$o['amount'],2) ?>
          <?= h($o['currency']) ?>
        </strong>
      </p>

      <p>
        طريقة الاستلام:
        <strong>
          <?= h($o['delivery_method'] === 'pickup'
              ? 'استلام من نقطة'
              : 'توصيل') ?>
        </strong>
      </p>

      <?php if($o['delivery_method']==='pickup'): ?>
        <p>
          نقطة الاستلام:
          <?= h($o['pickup_location']) ?>
        </p>
      <?php else: ?>
        <p>
          العنوان:
          <?= h($o['address']) ?>
        </p>
      <?php endif; ?>

      <p class="muted">
        سيتم التواصل معك لتأكيد موعد التسليم/الاستلام.<br>
        الدفع عند الاستلام 💵
      </p>

      <a class="btn" href="index.php">
        🛍️ العودة للمتجر
      </a>

    <?php endif; ?>
  </div>
</body>
</html>
