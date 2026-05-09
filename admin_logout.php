<?php
// تحميل ملف الإعدادات العامة (مثل BASE_URL)
require_once __DIR__ . '/config.php';

// إذا لم تكن الجلسة مفعّلة، ابدأ جلسة جديدة
if (session_status() === PHP_SESSION_NONE) session_start();

// حذف بيانات المستخدم من الـ session (تسجيل خروج)
unset($_SESSION['user']);

// تدمير الجلسة بالكامل من السيرفر
session_destroy();

// تحديد الرابط الأساسي للموقع
$base = defined('BASE_URL') ? BASE_URL : '/';

// إعادة توجيه المستخدم للصفحة الرئيسية
header('Location: ' . $base . 'index.php');

// إيقاف تنفيذ الملف نهائيًا
exit;