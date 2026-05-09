<?php

// هذا الملف يعمل كـ API للمنتجات
// - يرجّع المنتجات بصيغة JSON
// - يسمح للأدمن بإضافة / تعديل / حذف منتجات

// تحميل ملف الإعدادات (BASE_URL وغيرها)
require_once __DIR__ . '/../config.php';

// تحميل ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/../db.php';

// تحديد نوع الرد على أنه JSON
header('Content-Type: application/json; charset=utf-8');

try {

  // إنشاء اتصال بقاعدة البيانات
  $pdo = db();

  // جعل أخطاء PDO ترمي Exceptions (للتصحيح)
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // معرفة نوع الطلب (GET / POST / DELETE)
  $method = $_SERVER['REQUEST_METHOD'];

  // تنفيذ الكود حسب نوع الطلب
  switch ($method) {

    /* ================== GET ================== */
    case 'GET':

      // جلب جميع المنتجات من قاعدة البيانات
      $stmt = $pdo->query("
        SELECT id, name, price, stock, image_url
        FROM products
        ORDER BY id DESC
      ");

      // تحويل النتائج إلى مصفوفة
      $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // إرسال المنتجات بصيغة JSON
      //تحويل مصفوفة PHP → JSON.
      echo json_encode($products, JSON_UNESCAPED_UNICODE);
      break;

    /* ================== POST ================== */
    case 'POST':

      // تحديد نوع العملية (إضافة أو تعديل)
      $action = $_POST['action'] ?? 'save';

      // ID المنتج (يُستخدم عند التعديل)
      $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

      // اسم المنتج
      $name = trim($_POST['name'] ?? '');

      // سعر المنتج
      $price = floatval($_POST['price'] ?? 0);

      // كمية المنتج
      $stock = intval($_POST['stock'] ?? 0);

      // رابط الصورة (اختياري)
      $image_url = trim($_POST['image_url'] ?? '');

      // التحقق من صحة البيانات الأساسية
      if ($name === '' || $price <= 0) {
        //خطأ من المستخدم.
        http_response_code(400);
        echo json_encode(['error' => '⚠️ بيانات المنتج غير صالحة.']);
        exit;
      }

      /* ======== رفع صورة المنتج (إن وُجدت) ======== */
      if (!empty($_FILES['image']['tmp_name'])) {

        // مسار مجلد الصور
        $uploadDir = __DIR__ . '/../uploads/';

        // إنشاء المجلد إذا لم يكن موجودًا
        if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }

        // إنشاء اسم فريد للصورة
        $filename = uniqid() . '-' . basename($_FILES['image']['name']);

        // المسار الكامل للصورة
        $targetPath = $uploadDir . $filename;

        // نقل الصورة من المسار المؤقت إلى المجلد
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {

          // حفظ رابط الصورة لاستخدامه لاحقًا
          $image_url = BASE_URL . 'uploads/' . $filename;
        }
      }

      /* ======== إضافة منتج جديد ======== */
      if ($action === 'create' || !$id) {

        // تجهيز استعلام إدخال منتج
        $stmt = $pdo->prepare("
          INSERT INTO products (name, price, stock, image_url)
          VALUES (?, ?, ?, ?)
        ");

        // تنفيذ الاستعلام
        $stmt->execute([$name, $price, $stock, $image_url]);

        // رد نجاح
        echo json_encode([
          'success' => true,
          'message' => '✅ تم إضافة المنتج بنجاح'
        ]);
        exit;
      }

      /* ======== تحديث منتج موجود ======== */
      if ($action === 'update' && $id) {

        // تجهيز استعلام التحديث
        $stmt = $pdo->prepare("
          UPDATE products
          SET name=?, price=?, stock=?, image_url=?
          WHERE id=?
        ");

        // تنفيذ التحديث
        $stmt->execute([$name, $price, $stock, $image_url, $id]);

        // رد نجاح
        echo json_encode([
          'success' => true,
          'message' => '✅ تم تحديث المنتج'
        ]);
        exit;
      }

      // في حال لم تنطبق أي عملية
      echo json_encode([
        'success' => false,
        'message' => '❌ لم يتم تنفيذ أي إجراء'
      ]);
      break;

    /* ================== DELETE ================== */
    case 'DELETE':

      // قراءة بيانات طلب DELETE
      parse_str(file_get_contents("php://input"), $_DELETE);

      // جلب ID المنتج من الرابط أو من بيانات DELETE
      $id = intval($_GET['id'] ?? ($_DELETE['id'] ?? 0));

      // التحقق من صحة ID
      if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => '⚠️ معرف المنتج غير صالح.']);
        exit;
      }

      // حذف المنتج من قاعدة البيانات
      $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
      $stmt->execute([$id]);

      // رد نجاح
      echo json_encode([
        'success' => true,
        'message' => '🗑️ تم حذف المنتج بنجاح'
      ]);
      break;

    /* ================== طريقة غير مدعومة ================== */
    default:
      http_response_code(405);
      echo json_encode(['error' => 'طريقة الطلب غير مسموحة.']);
  }
//Throwableيشمل:Exception,Error
} catch (Throwable $e) {

  // في حال حدوث أي خطأ غير متوقع
  http_response_code(500);
  echo json_encode([
    'error' => 'حدث خطأ في الخادم: ' . $e->getMessage()
  ]);
}
