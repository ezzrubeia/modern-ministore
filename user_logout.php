<?php

// تحميل ملف الإعدادات العامة (للحصول على BASE_URL)
require_once __DIR__ . '/config.php';

// تشغيل الـ session إذا لم تكن مفعّلة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تفريغ جميع بيانات الـ session (حذف بيانات المستخدم)
$_SESSION = [];

// تدمير الـ session بالكامل من السيرفر
session_destroy();

// تحديد المسار الأساسي للمشروع
$base = defined('BASE_URL') ? BASE_URL : '/';

// إعادة توجيه المستخدم إلى الصفحة الرئيسية بعد تسجيل الخروج
header('Location: ' . $base . 'index.php');

// إيقاف تنفيذ السكربت بعد التوجيه
exit;
