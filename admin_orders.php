<?php
/* تحميل ملف الإعدادات العامة (مثل BASE_URL) */
require_once __DIR__ . '/config.php';

/* بدء الجلسة إذا لم تكن مفعّلة */
if (session_status() === PHP_SESSION_NONE) session_start();

/* تحميل الاتصال بقاعدة البيانات */
require_once __DIR__ . '/db.php';

/* تحميل ملف التحقق من الصلاحيات */
require_once __DIR__ . '/auth.php';

/* التأكد أن المستخدم أدمن */
require_admin(); 

/* إنشاء اتصال PDO */
$pdo = db();

/* ===============================
   معالجة تحديث حالة الطلب
=============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {

    /* تحويل رقم الطلب إلى رقم صحيح */
    $order_id = (int)$_POST['order_id'];

    /* قراءة الحالة الجديدة */
    $status = trim($_POST['status']);

    /* الحالات المسموح بها فقط */
    $allowed = ['قيد التأكيد', 'قيد التنفيذ', 'مكتمل', 'ملغى'];

    /* التحقق أن الحالة ضمن المسموح */
    if (in_array($status, $allowed, true)) {

        /* تجهيز استعلام تحديث الحالة */
        $stmt = $pdo->prepare(
            "UPDATE orders SET status = :s WHERE id = :id"
        );

        /* تنفيذ التحديث */
        $stmt->execute([
            ':s'  => $status,
            ':id' => $order_id
        ]);

        /* رسالة نجاح في الجلسة */
        $_SESSION['flash'] = "✅ تم تحديث حالة الطلب رقم #$order_id";

        /* إعادة تحميل الصفحة */
        header('Location: admin_orders.php');
        exit;
    }
}

/* ===============================
   جلب جميع الطلبات
=============================== */
$stmt = $pdo->query("
    SELECT 
        o.id,                     -- رقم الطلب
        o.customer_name,          -- اسم العميل
        o.customer_phone,         -- هاتف العميل
        o.amount,                 -- إجمالي المبلغ
        o.status,                 -- حالة الطلب
        o.created_at,             -- تاريخ الإنشاء
        o.delivery_method,        -- طريقة الاستلام
        o.address,                -- عنوان التوصيل
        o.pickup_location,        -- نقطة الاستلام
        u.name AS user_name       -- اسم المستخدم (إن وجد)
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
");

/* تحويل النتائج إلى مصفوفة */
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>

    <!-- ترميز الصفحة -->
    <meta charset="UTF-8">

    <!-- عنوان الصفحة -->
    <title>إدارة الطلبات</title>

    <!-- تحميل CSS -->
    <link rel="stylesheet" href="style.css">

    <style>
        /* تنسيق عام للصفحة */
        body {
            background: linear-gradient(145deg, #4b0082, #7a2ff7);
            color: #fff;
            font-family: "Cairo", sans-serif;
        }

        /* عنوان الصفحة */
        h1 {
            text-align:center;
            margin-top: 20px;
        }

        /* جدول الطلبات */
        table {
            width: 95%;
            margin: 20px auto;
            border-collapse: collapse;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        /* خلايا الجدول */
        th, td {
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding: 10px;
            text-align: center;
        }

        /* رأس الجدول */
        th {
            background: rgba(255,255,255,0.15);
        }

        /* تأثير hover على الصف */
        tr:hover { background: rgba(255,255,255,0.08); }

        /* select و button */
        select, button {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
        }

        /* القائمة المنسدلة */
        select { background: #fff; color: #000; }

        /* زر التحديث */
        button {
            background: #7c3aed;
            color: #fff;
            cursor: pointer;
        }

        /* hover على الزر */
        button:hover { background: #9f67ff; }

        /* رسالة التنبيه */
        .flash {
            text-align:center;
            margin: 10px;
            background: rgba(0,0,0,0.4);
            display: inline-block;
            padding: 10px 18px;
            border-radius: 10px;
        }

        /* زر الرجوع */
        a.back {
            color: #fff;
            text-decoration: none;
            background: #22c55e;
            padding: 8px 14px;
            border-radius: 8px;
            display:inline-block;
            margin: 10px 0;
        }
    </style>
</head>

<body>

<!-- عنوان الصفحة -->
<h1>📦 إدارة الطلبات</h1>

<!-- زر الرجوع للوحة التحكم -->
<div style="text-align:center;">
    <a href="admin_dashboard.php" class="back">⬅ العودة للوحة التحكم</a>
</div>

<!-- عرض رسالة التنبيه إن وجدت -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- في حال لا توجد طلبات -->
<?php if (empty($orders)): ?>
    <p style="text-align:center;">لا توجد طلبات حالياً.</p>
<?php else: ?>

<!-- جدول الطلبات -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>العميل</th>
            <th>الهاتف</th>
            <th>المستخدم</th>
            <th>الإجمالي</th>
            <th>طريقة الاستلام</th>
            <th>الحالة</th>
            <th>تغيير الحالة</th>
            <th>تاريخ الإنشاء</th>
        </tr>
    </thead>
    <tbody>

    <!-- عرض كل طلب -->
    <?php foreach ($orders as $o): ?>
        <tr>

            <!-- رقم الطلب -->
            <td><?= $o['id'] ?></td>

            <!-- اسم العميل -->
            <td><?= htmlspecialchars($o['customer_name']) ?></td>

            <!-- هاتف العميل -->
            <td><?= htmlspecialchars($o['customer_phone']) ?></td>

            <!-- اسم المستخدم -->
            <td><?= htmlspecialchars($o['user_name'] ?? '-') ?></td>

            <!-- إجمالي الطلب -->
            <td><?= number_format($o['amount'],2) ?> USD</td>

            <!-- طريقة الاستلام -->
            <td>
                <?= $o['delivery_method'] === 'pickup'
                    ? 'استلام من نقطة (' . htmlspecialchars($o['pickup_location'] ?? '-') . ')'
                    : 'توصيل (' . htmlspecialchars($o['address'] ?? '-') . ')' ?>
            </td>

            <!-- حالة الطلب -->
            <td><strong><?= htmlspecialchars($o['status']) ?></strong></td>

            <!-- فورم تغيير الحالة -->
            <td>
                <form method="post" style="display:flex; gap:6px; justify-content:center;">

                    <!-- رقم الطلب -->
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">

                    <!-- اختيار الحالة -->
                    <select name="status" required>
                        <option value="قيد التأكيد" <?= $o['status']==='قيد التأكيد'?'selected':'' ?>>قيد التأكيد</option>
                        <option value="قيد التنفيذ" <?= $o['status']==='قيد التنفيذ'?'selected':'' ?>>قيد التنفيذ</option>
                        <option value="مكتمل" <?= $o['status']==='مكتمل'?'selected':'' ?>>مكتمل</option>
                        <option value="ملغى" <?= $o['status']==='ملغى'?'selected':'' ?>>ملغى</option>
                    </select>

                    <!-- زر التحديث -->
                    <button type="submit">تحديث</button>
                </form>
            </td>

            <!-- تاريخ إنشاء الطلب -->
            <td><?= htmlspecialchars($o['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>
<?php endif; ?>

</body>
</html>
