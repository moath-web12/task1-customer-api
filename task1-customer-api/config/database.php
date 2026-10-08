<?php
// config/database.php

/**
 * فئة التعامل مع اتصال قاعدة البيانات (Database Class)
 * تقوم بإدارة وإنشاء الاتصال بقاعدة بيانات MySQL باستخدام مكتبة PDO
 */
class Database {
    // إعدادات الاتصال بقاعدة البيانات
    private $host = "localhost";      // اسم المستضيف (غالباً ما يكون localhost في البيئة المحلية)
    private $db_name = "internship_db"; // اسم قاعدة البيانات
    private $username = "root";       // اسم المستخدم لقاعدة البيانات
    private $password = "";          // كلمة المرور الخاصة بقاعدة البيانات

    /**
     * متغير لتخزين كائن الاتصال بقاعدة البيانات
     * @var PDO|null
     */
    public $conn;

    /**
     * دالة إنشاء وإنشاء الاتصال بقاعدة البيانات
     * 
     * @return PDO|null يعيد كائن PDO عند نجاح الاتصال
     */
    public function getConnection() {
        // إعادة تعيين متغير الاتصال إلى قيمة فارغة قبل المحاولة
        $this->conn = null;

        try {
            // إنشاء اتصال جديد باستخدام مكتبة PDO لضمان الأمان والأداء العالي
            // يتم تحديد المستضيف، اسم قاعدة البيانات، ونوع التشفير (utf8mb4 لدعم اللغة العربية والرموز التعبيرية)
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );

            // ضبط نمط معالجة الأخطاء لإطلاق الاستثناءات (Exceptions) عند حدوث أي خطأ في الاستعلامات
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $exception) {
            // في حال حدوث خطأ أثناء الاتصال، يتم إرجاع استجابة بتنسيق JSON موضحاً فيها الخطأ
            echo json_encode([
                "status" => false,
                "message" => "Database connection error: " . $exception->getMessage()
            ]);

            // إنهاء تنفيذ السكريبت فوراً لمنع متابعة الأوامر مع وجود خطأ في الاتصال
            exit();
        }

        // إرجاع كائن الاتصال النشط لاستخدامه في الاستعلامات
        return $this->conn;
    }
}