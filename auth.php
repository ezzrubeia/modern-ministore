<?php

// إذا لم تكن الجلسة مفعّلة، نبدأ session جديدة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// دالة تتحقق هل المستخدم مسجّل دخول أم لا
//الهدف:ضمان أن الجلسة تعمل قبل استخدام ($_SESSION)
function is_logged_in(): bool
{
    // نتحقق من وجود بيانات المستخدم ووجود id خاص به داخل الجلسة
    return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

// دالة ترجع رقم المستخدم الحالي
function current_user_id(): int
{
    // إذا كان المستخدم مسجّل دخول نرجع id تبعه
    // وإذا لم يكن مسجّل دخول نرجع 0
    return is_logged_in() ? (int)$_SESSION['user']['id'] : 0;
}

// دالة تفرض تسجيل الدخول قبل دخول الصفحة
function require_login(): void
{
    // إذا لم يكن المستخدم مسجّل دخول
    if (!is_logged_in()) {

        // حفظ الصفحة الحالية لإرجاع المستخدم لها بعد تسجيل الدخول
        $next = isset($_SERVER['REQUEST_URI']) ? urlencode($_SERVER['REQUEST_URI']) : '';

        // تحديد الرابط الأساسي للمشروع
        // إذا كان BASE_URL معرف نستخدمه، وإلا نستخدم /
        $base = defined('BASE_URL') ? BASE_URL : '/';

        // تحويل المستخدم إلى صفحة تسجيل الدخول
        // مع تمرير رابط الصفحة الحالية كـ next 
        header('Location: ' . $base . 'user_login.php' . ($next ? ('?next=' . $next) : ''));
        exit;
    }
}

// دالة تتحقق هل المستخدم أدمن
function is_admin(): bool {

    // نتحقق من وجود المستخدم
    // ثم نتحقق أن قيمة role تساوي 'admin'
    return !empty($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin';
}


//تعريف دالة لحماية صفحات الأدمن
function require_admin(): void {

    // إذا لم يكن المستخدم أدمن
    if (!is_admin()) {

        // تحديد الرابط الأساسي للمشروع
        $base = defined('BASE_URL') ? BASE_URL : '/';

        // تحويل المستخدم إلى صفحة تسجيل الدخول
        header('Location: ' . $base . 'user_login.php');
        exit;
    }
}
