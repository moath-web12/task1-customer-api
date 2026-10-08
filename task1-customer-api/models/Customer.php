<?php
// models/Customer.php

/**
 * فئة التعامل مع بيانات العملاء (Customer Model)
 * تتولى جميع عمليات قاعدة البيانات المتعلقة بجدول العملاء (CRUD Operations + Search)
 */
class Customer {
    /**
     * @var PDO كائن الاتصال بقاعدة البيانات
     */
    private $conn;

    /**
     * @var string اسم جدول العملاء في قاعدة البيانات
     */
    private $table_name = "customers";

    /**
     * منشئ الفئة (Constructor)
     * 
     * @param PDO $db كائن الاتصال الممرر لقاعدة البيانات
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * جلب جميع العملاء مرتبين تنازلياً حسب المعرف (ID)
     * 
     * @return PDOStatement كائن الاستعلام المنفذ لجلب البيانات
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * جلب بيانات عميل واحد عن طريق المعرف (ID)
     * 
     * @param int $id معرف العميل المطلوب
     * @return array|false مصفوفة تحتوي على بيانات العميل أو false في حال عدم وجوده
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * التحقق مما إذا كان رقم الهاتف مسجلاً مسبقاً في قاعدة البيانات
     * 
     * @param string $phone رقم الهاتف المراد التحقق منه
     * @param int|null $exclude_id معرف عميل يتم استثناؤه من التحقق (مفيد أثناء عملية التحديث)
     * @return bool true إذا كان الرقم موجوداً، أو false إذا كان غير موجود
     */
    public function isPhoneExists($phone, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE phone = :phone";
        
        // استثناء العميل الحالي أثناء عملية التحديث لتجنب التعارض مع رقمه نفسه
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':phone', $phone);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * إنشاء سجل عميل جديد في قاعدة البيانات
     * 
     * @param array $data مصفوفة تحتوي على بيانات العميل (name, phone, email, status)
     * @return string|false معرف العميل الجديد (ID) في حال النجاح، أو false عند الفشل
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " (name, phone, email, status) VALUES (:name, :phone, :email, :status)";
        $stmt = $this->conn->prepare($query);

        // تنظيف البيانات من النصوص والوسوم غير المرغوب فيها لحماية التطبيق (Sanitization)
        $name = htmlspecialchars(strip_tags($data['name']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $email = isset($data['email']) ? htmlspecialchars(strip_tags($data['email'])) : null;
        $status = isset($data['status']) ? htmlspecialchars(strip_tags($data['status'])) : 'active';

        // ربط المعاملات بالاستعلام
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':status', $status);

        // تنفيذ الاستعلام وإعادة المعرف الجديد
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * تحديث بيانات عميل حالي
     * 
     * @param int $id معرف العميل المراد تعديله
     * @param array $data المصفوفة التي تحتوي على البيانات الجديدة
     * @return bool true في حال نجاح التحديث، false في حال الفشل
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, phone = :phone, email = :email, status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // تنظيف البيانات قبل التحديث
        $name = htmlspecialchars(strip_tags($data['name']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $email = isset($data['email']) ? htmlspecialchars(strip_tags($data['email'])) : null;
        $status = isset($data['status']) ? htmlspecialchars(strip_tags($data['status'])) : 'active';

        // ربط القيم بالمعاملات
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':status', $status);

        return $stmt->execute();
    }

    /**
     * حذف عميل من قاعدة البيانات بواسطة المعرف (ID)
     * 
     * @param int $id معرف العميل المراد حذفه
     * @return bool true في حال نجاح الحذف، false في حال الفشل
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * البحث عن العملاء باستعمال خيارات متعددة (الهاتف، الاسم، البريد الإلكتروني)
     * 
     * @param string|null $phone رقم الهاتف للبحث (جزئي أو كامل)
     * @param string|null $name اسم العميل للبحث (جزئي أو كامل)
     * @param string|null $email البريد الإلكتروني للبحث (جزئي أو كامل)
     * @return PDOStatement كائن الاستعلام المنفذ لن نتائج البحث
     */
    public function search($phone = null, $name = null, $email = null) {
        // البدء بشرط 1=1 لتسهيل إضافة شروط الديناميكية باستخدام AND
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = [];

        // إضافة شرط بحث بالهاتف عند توفره
        if (!empty($phone)) {
            $query .= " AND phone LIKE :phone";
            $params[':phone'] = "%" . $phone . "%";
        }
        
        // إضافة شرط بحث بالاسم عند توفره
        if (!empty($name)) {
            $query .= " AND name LIKE :name";
            $params[':name'] = "%" . $name . "%";
        }

        // إضافة شرط بحث بالبريد الإلكتروني عند توفره
        if (!empty($email)) {
            $query .= " AND email LIKE :email";
            $params[':email'] = "%" . $email . "%";
        }

        $query .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);
        
        // ربط القيم الديناميكية بالاستعلام لتفادي ثغرات SQL Injection
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        return $stmt;
    }
}