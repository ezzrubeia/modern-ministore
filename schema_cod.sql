-- =========================================
-- جدول المنتجات (products)
-- =========================================

-- إنشاء جدول المنتجات إذا لم يكن موجودًا
CREATE TABLE IF NOT EXISTS products (
  -- id: معرف المنتج (مفتاح أساسي) يزيد تلقائيًا
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- name: اسم المنتج (إجباري)
  name VARCHAR(255) NOT NULL,

  -- price: سعر المنتج بدقة خانتين بعد الفاصلة (إجباري) وقيمته الافتراضية 0
  price DECIMAL(10,2) NOT NULL DEFAULT 0,

  -- stock: المخزون العام للمنتج (إجباري) وقيمته الافتراضية 0
  stock INT NOT NULL DEFAULT 0,

  -- image_url: رابط صورة المنتج (اختياري، يسمح بـ NULL)
  image_url VARCHAR(500) NULL,

  -- created_at: وقت إنشاء السجل، افتراضيًا وقت الإدخال الحالي
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
-- محرك InnoDB لدعم العلاقات والمعاملات + ترميز utf8mb4 لدعم العربية
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- جدول المستخدمين (users)
-- =========================================

-- إنشاء جدول المستخدمين إذا لم يكن موجودًا
CREATE TABLE IF NOT EXISTS users (
  -- id: معرف المستخدم (مفتاح أساسي) يزيد تلقائيًا
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- name: اسم المستخدم (إجباري)
  name VARCHAR(255) NOT NULL,

  -- email: بريد المستخدم (إجباري) وفريد لمنع التكرار
  email VARCHAR(255) NOT NULL UNIQUE,

  -- password_hash: هاش كلمة المرور (وليس كلمة المرور نفسها) (إجباري)
  password_hash VARCHAR(255) NOT NULL,

  -- created_at: وقت إنشاء الحساب، افتراضيًا الآن
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- جدول الطلبات (orders)
-- =========================================

-- إنشاء جدول الطلبات إذا لم يكن موجودًا
CREATE TABLE IF NOT EXISTS orders (
  -- id: معرف الطلب (مفتاح أساسي) يزيد تلقائيًا
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- user_id: المستخدم المرتبط بالطلب (اختياري: NULL يسمح بطلبات بدون مستخدم/طلبات قديمة)
  user_id INT NULL,

  -- gateway_order_id: رقم مرجعي من بوابة الدفع/مرجع خارجي (إجباري)
  gateway_order_id VARCHAR(200) NOT NULL,

  -- status: حالة الطلب (إجباري)
  status VARCHAR(50) NOT NULL,

  -- currency: العملة (إجباري)
  currency VARCHAR(10) NOT NULL,

  -- amount: إجمالي مبلغ الطلب (إجباري)
  amount DECIMAL(10,2) NOT NULL,

  -- customer_name: اسم العميل (إجباري)
  customer_name VARCHAR(255) NOT NULL,

  -- customer_phone: هاتف العميل (إجباري) كنص لدعم + و 0 بالبداية
  customer_phone VARCHAR(50) NOT NULL,

  -- delivery_method: طريقة التوصيل (delivery/pickup...) (إجباري)
  delivery_method VARCHAR(20) NOT NULL,

  -- address: عنوان التوصيل (اختياري، مناسب عند delivery)
  address VARCHAR(500) NULL,

  -- pickup_location: مكان الاستلام (اختياري، مناسب عند pickup)
  pickup_location VARCHAR(255) NULL,

  -- notes: ملاحظات إضافية (اختياري)
  notes TEXT NULL,

  -- created_at: وقت إنشاء الطلب
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  -- index على user_id لتسريع الاستعلامات (جلب طلبات المستخدم/الربط مع users)
  INDEX idx_orders_user_id (user_id),

  -- تعريف قيد FK يربط orders.user_id بـ users.id
  -- عند حذف المستخدم: نجعل user_id = NULL بدل حذف الطلب
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE SET NULL
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- جدول عناصر الطلب (order_items)
-- =========================================

-- إنشاء جدول عناصر الطلب إذا لم يكن موجودًا
CREATE TABLE IF NOT EXISTS order_items (
  -- id: معرف عنصر الطلب (مفتاح أساسي) يزيد تلقائيًا
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- order_id: رقم الطلب الذي ينتمي له هذا العنصر (إجباري)
  order_id INT NOT NULL,

  -- product_id: معرف المنتج (اختياري هنا) — يسمح بـ NULL إذا حابب تحفظ العنصر حتى لو انحذف المنتج
  product_id INT NULL,

  -- name: اسم المنتج وقت الطلب 
  name VARCHAR(255),

  -- qty: الكمية المطلوبة (إجباري)
  qty INT NOT NULL,

  -- unit_price: سعر الوحدة وقت الطلب (إجباري) لتثبيت السعر حتى لو تغير لاحقًا
  unit_price DECIMAL(10,2) NOT NULL,

  -- ربط عنصر الطلب بالطلب الأساسي
  -- عند حذف الطلب: احذف كل عناصره تلقائيًا
  FOREIGN KEY (order_id)
    REFERENCES orders(id)
    ON DELETE CASCADE
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================
-- Seed Data: إدخال منتجات تجريبية
-- =========================================

-- إدخال منتجات ابتدائية
INSERT INTO products (name, price, stock, image_url) VALUES
  ('قلم حبر', 2.50, 100, 'https://picsum.photos/seed/pen/400/300'),
  ('دفتر ملاحظات', 5.90, 80, 'https://picsum.photos/seed/notebook/400/300'),
  ('زجاجة ماء', 3.20, 60, 'https://picsum.photos/seed/bottle/400/300')
-- في حال صار تعارض بسبب مفتاح فريد/أساسي، حدّث الاسم بالقيمة الجديدة
-- (تنبيه: هذا لن يتفعل إلا إذا كان هناك UNIQUE/PK يسبب التعارض)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- =========================================
-- جدول ألوان/نسخ المنتجات (product_variants)
-- =========================================

-- إنشاء جدول variants إذا لم يكن موجودًا
CREATE TABLE IF NOT EXISTS product_variants (
  -- id: معرف variant (مفتاح أساسي) يزيد تلقائيًا
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- product_id: المنتج المرتبط بهذا اللون/النسخة (إجباري)
  product_id INT NOT NULL,

  -- color: اسم/قيمة اللون (إجباري)
  color VARCHAR(50) NOT NULL,

  -- stock: مخزون هذا اللون/النسخة (إجباري) افتراضيًا 0
  stock INT NOT NULL DEFAULT 0,

  -- فريد مركب يمنع تكرار نفس اللون لنفس المنتج
  UNIQUE KEY uniq_product_color (product_id, color),

  -- FK يربط variants بالمنتجات
  -- عند حذف المنتج: احذف كل variants التابعة له
  CONSTRAINT fk_variant_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
);

-- =========================================
-- تعديل جدول products لإضافة description
-- =========================================

-- بدء تعديل جدول products
ALTER TABLE products
-- إضافة عمود description من نوع TEXT بعد عمود name (ترتيب شكلي)
ADD COLUMN description TEXT AFTER name;
