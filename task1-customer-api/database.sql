-- ===================================================
-- Database Setup
-- ===================================================

-- collation: يحدد قواعد المقارنة والترتيب للنصوص
-- unicode: تعني استخدام معايير unicode العالمية للمقارنة بين الحروف
-- ci (Case Insensitive): عدم التفرقة بين الحروف الكبيرة أو الصغيرة
CREATE DATABASE IF NOT EXISTS `internship_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- USE: أمر يعلم الخادم بضرورة الدخول واختيار قاعدة البيانات لتنفيذ الأوامر
USE `internship_db`;


-- ===================================================
-- Table Structure for `customers`
-- ===================================================

-- CREATE TABLE IF NOT EXISTS: أمر إنشاء جدول جديد إذا لم يكن موجود من قبل
CREATE TABLE IF NOT EXISTS `customers` (
    -- id: المعرف الفريد للعميل
    -- INT: نوع البيانات عدد صحيح
    -- AUTO_INCREMENT: يزيد العدد 1 تلقائياً مع كل سجل جديد
    -- PRIMARY KEY: المفتاح الأساسي للجدول
    `id` INT AUTO_INCREMENT PRIMARY KEY,

    -- name: اسم العميل
    -- VARCHAR(100): نص لغاية 100 حرف
    -- NOT NULL: شرط إجباري يمنع ترك الحقل فارغاً
    `name` VARCHAR(100) NOT NULL,

    -- phone: رقم هاتف العميل
    -- UNIQUE: قيد هام جداً يمنع تكرار رقم الهاتف نهائياً في الجدول
    `phone` VARCHAR(20) NOT NULL UNIQUE,

    -- email: البريد الإلكتروني
    -- NULL: حقل اختياري، يمكن ترك القيمة فارغة
    `email` VARCHAR(100) NULL,

    -- status: حالة العميل
    -- ENUM('active', 'inactive'): حقل خاص يفرض خيارات محدودة فقط
    -- DEFAULT 'active': القيمة الافتراضية؛ إذا لم نرسل حالة العميل، تكون active تلقائياً
    `status` ENUM('active', 'inactive') DEFAULT 'active',

    -- created_at: تاريخ ووقت إنشاء السجل
    -- TIMESTAMP: نوع بيانات يحفظ التاريخ والوقت
    -- DEFAULT CURRENT_TIMESTAMP: يسجل الوقت والتاريخ الحالي للسيرفر تلقائياً
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- updated_at: تاريخ ووقت آخر تعديل
    -- ON UPDATE CURRENT_TIMESTAMP: ميزة ممتازة تضمن تحديث الوقت والتاريخ تلقائياً عند التعديل
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) 
-- ENGINE=InnoDB: يحدد محرك التخزين للجدول
-- DEFAULT CHARSET=utf8mb4: يضمن تطبيق ترميز النصوص الخاص بجميع الحروف واللغات على مستوى هذا الجدول تحديداً
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*
يمكنك تنفيذ هذا الاستعلام السريع في phpMyAdmin لإضافة بيانات تجريبية لتبدأ بالتجربة عليها فوراً
INSERT INTO `customers` (`name`, `phone`, `email`, `status`) VALUES
('سعد علي العماري', '0501234567', 'saad@example.com', 'active'),
('محمد أحمد', '0559876543', 'mohammed@example.com', 'active');
*/