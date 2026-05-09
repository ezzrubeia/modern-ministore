<?php

// تعريف ثابت DB_HOST: اسم السيرفر/المضيف لقاعدة البيانات (هنا قاعدة البيانات على نفس الجهاز)
define('DB_HOST', 'localhost');

// تعريف ثابت DB_NAME: اسم قاعدة البيانات التي سيستخدمها المشروع داخل MySQL
define('DB_NAME', 'mini_market_project');

// تعريف ثابت DB_USER: اسم مستخدم الافتراضيMySQL (في بيئات التطوير غالبًا يكون root)
define('DB_USER', 'root');

// تعريف ثابت DB_PASS: كلمة مرور مستخدم MySQL (فارغة هنا لأن XAMPP غالبًا بدون كلمة مرور افتراضيًا)
define('DB_PASS', '');

// تعريف ثابت DB_CHARSET: ترميز الاتصال بقاعدة البيانات (utf8mb4 لدعم العربية والإيموجي)
define('DB_CHARSET', 'utf8mb4');

// تعريف ثابت BASE_URL: الرابط الأساسي للمشروع لتوليد الروابط (redirect/روابط صفحات/روابط API) بدون تكرار المسار
define('BASE_URL', 'http://localhost/mini_market_project/');

// ضبط المنطقة الزمنية الافتراضية في PHP حتى تكون كل التواريخ/الأوقات بتوقيت (Asia/Hebron) بدل UTC
date_default_timezone_set('Asia/Hebron');
