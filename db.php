<?php

// إذا لم تكن الدالة h موجودة مسبقًا (لتجنب إعادة تعريفها)
if (!function_exists('h')) {

    // دالة h: لتأمين أي نص قبل طباعته في HTML (حماية من XSS)
    function h($s) {

        // htmlspecialchars:
        // يحوّل الأحرف الخطيرة (< > " ') إلى نص آمن
        // $s ?? '' : إذا كانت القيمة null نستخدم نص فارغ
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// دالة db: ترجع اتصال واحد بقاعدة البيانات باستخدام PDO
function db() {

    // متغير static يحتفظ بالاتصال ولا يُعاد إنشاؤه كل مرة
    static $pdo;

    // إذا كان الاتصال موجود مسبقًا، نرجعه مباشرة
    if ($pdo instanceof PDO) return $pdo;

    // تحميل ملف الإعدادات (بيانات قاعدة البيانات)
    require_once __DIR__ . '/config.php';

    // تجهيز نص الاتصال بقاعدة البيانات (DSN)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    // محاولة الاتصال بقاعدة البيانات
    try {

        // إنشاء اتصال PDO جديد
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [

            // أي خطأ في SQL يتحول إلى Exception
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            //   [اسماء]نتائج الاستعلامات ترجع كمصفوفة    
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    //اذا صار خطا في الاتصال ينتقل هنا 
    } catch (PDOException $e) {

        // في حال فشل الاتصال: إظهار رسالة خطأ وإيقاف التنفيذ
        die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    }

    // إرجاع اتصال قاعدة البيانات الجاهز
    return $pdo;
}
