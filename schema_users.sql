-- إنشاء جدول users إذا لم يكن موجودًا مسبقًا
CREATE TABLE IF NOT EXISTS users (
  -- عمود id: رقم تعريفي لكل مستخدم، يزيد تلقائيًا، وهو المفتاح الأساسي
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- عمود name: اسم المستخدم، نص حتى 255 حرف، ولا يُسمح أن يكون NULL
  name VARCHAR(255) NOT NULL,

  -- عمود email: البريد الإلكتروني، نص حتى 255 حرف، إجباري، ويجب أن يكون فريدًا (لا يتكرر)
  email VARCHAR(255) NOT NULL UNIQUE,

  -- عمود password_hash: تخزين "هاش" كلمة المرور (وليس كلمة المرور نفسها)، إجباري
  password_hash VARCHAR(255) NOT NULL,

  -- عمود created_at: وقت إنشاء الحساب، افتراضيًا يضع وقت الإدخال الحالي تلقائيًا
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
-- استخدام محرك InnoDB لدعم العلاقات (FK) والمعاملات، والترميز utf8mb4 لدعم العربية والإيموجي
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- فحص هل العمود user_id موجود في جدول orders أم لا (حتى لا نحاول إضافته مرتين)
SET @col_exists := (
  SELECT COUNT(*)
  FROM information_schema.columns     -- هذا جدول نظام داخلي في MySQL يحتوي معلومات عن الأعمدة في كل الجداول.
  WHERE table_schema = DATABASE()     -- قاعدة البيانات الحالية
    AND table_name = 'orders'         -- جدول الطلبات
    AND column_name = 'user_id'       -- العمود المطلوب فحصه
);

-- بناء أمر ديناميكي:
-- إذا لم يكن user_id موجودًا: أضفه + أضف index عليه لتسريع الاستعلامات
-- إذا كان موجودًا: نفّذ SELECT 1 فقط لتجنب الأخطاء
SET @ddl := IF(
  @col_exists = 0,
  'ALTER TABLE orders ADD COLUMN user_id INT NULL, ADD INDEX idx_orders_user_id (user_id)',
  'SELECT 1'
);

-- تحضير الأمر الموجود في @ddl كـ prepared statement
PREPARE stmt FROM @ddl;

-- تنفيذ الأمر المحضّر (إما ALTER TABLE أو SELECT 1)
EXECUTE stmt;

-- تحرير/تنظيف prepared statement من الذاكرة
DEALLOCATE PREPARE stmt;

-- تحديد اسم الـ Foreign Key الذي سنضيفه (لاستخدامه في الفحص ومنع التكرار)
SET @fk_name := 'fk_orders_user';

-- فحص هل الـ Foreign Key بهذا الاسم موجود مسبقًا على جدول orders
SET @fk_exists := (
  SELECT COUNT(*)
  FROM information_schema.table_constraints
  WHERE constraint_schema = DATABASE()   -- قاعدة البيانات الحالية
    AND table_name = 'orders'            -- جدول الطلبات
    AND constraint_type = 'FOREIGN KEY'  -- نوع القيد: FK
    AND constraint_name = @fk_name       -- الاسم المطلوب
);

-- بناء أمر ديناميكي لإضافة الـ FK إذا لم يكن موجودًا:
-- user_id في orders يشير إلى id في users
-- عند حذف مستخدم: نضع user_id = NULL بدل حذف الطلب
SET @ddl2 := IF(
  @fk_exists = 0,
  CONCAT(
    'ALTER TABLE orders ADD CONSTRAINT ',
    @fk_name,
    ' FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL'
  ),
  'SELECT 1'
);

-- تحضير الأمر الموجود في @ddl2
PREPARE stmt2 FROM @ddl2;

-- تنفيذ الأمر (إما إضافة FK أو SELECT 1)
EXECUTE stmt2;

-- تنظيف prepared statement الثاني
DEALLOCATE PREPARE stmt2;

-- إضافة عمود role لتحديد نوع المستخدم (user أو admin)
-- افتراضيًا أي مستخدم جديد سيكون role = 'user'
ALTER TABLE users ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user';

-- إنشاء مستخدم أدمن افتراضي (قد يفشل إذا كان الإيميل موجود مسبقًا بسبب UNIQUE)
INSERT INTO users (name, email, password_hash, role) VALUES (
  'Administrator',
  'admin@store.com',
  '$2y$10$X0XH5q1QxE2V1X1UqF9g6uXy1m5QpYy9JqKcYzFZqK5s5gQk5Q0sS',
  'admin'
);
