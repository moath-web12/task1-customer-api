<?php
// api/customers.php

/**
 * نقطة النهاية لإدارة واجهة برمجة التطبيقات للعملاء (Customers REST API Endpoint)
 * تتولى استقبال الطلبات وتوجيهها لعمليات جلب، إنشاء، تحديث، وحذف العملاء (CRUD) بالإضافة للبحث
 */

// إظهار جميع الأخطاء والتنبيهات لتسهيل عملية التتبع والاختبار (Debugging)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ضبط ترويسات الاستجابة (CORS & Content-Type Headers)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// استدعاء ملف الاتصال بقاعدة البيانات وفئة العميل (Model)
require_once "../config/database.php";
require_once "../models/Customer.php";

// تهيئة كائن الاتصال بقاعدة البيانات وكائن نموذج العميل
$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);

// تحديد نوع طريقة الطلب (HTTP Method) وقراءة المسار (URL Path)
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
$request = explode('/', trim($path, '/'));

// استخراج معرف العميل (ID) سواءً تم إرساله في المسار أو كـ Query Parameter
$id = null;
if (isset($request[0]) && is_numeric($request[0])) {
    $id = intval($request[0]);
} elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
}

// =========================================================================
// 1. معالجة عمليات البحث (Search Operations)
// =========================================================================
if (isset($_GET['phone']) || isset($_GET['name']) || isset($_GET['email'])) {
    $phone = $_GET['phone'] ?? null;
    $name = $_GET['name'] ?? null;
    $email = $_GET['email'] ?? null;

    $stmt = $customer->search($phone, $name, $email);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($results);
    exit;
}

// =========================================================================
// 2. معالجة العمليات حسب نوع طريقة الطلب (HTTP Method Handler)
// =========================================================================
switch ($method) {
    /**
     * طلبات جلب البيانات (GET Method)
     * إما جلب بيانات عميل محدد عن طريق ID أو جلب كافة العملاء
     */
    case 'GET':
        if ($id) {
            $data = $customer->getById($id);
            if ($data) {
                http_response_code(200);
                echo json_encode($data);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Customer not found"]);
            }
        } else {
            $stmt = $customer->getAll();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode($data);
        }
        break;

    /**
     * طلبات إنشاء عميل جديد (POST Method)
     * تتطلب الاسم ورقم الهاتف، مع التحقق من عدم تكرار رقم الهاتف
     */
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['name']) || empty($data['phone'])) {
            http_response_code(400);
            echo json_encode(["error" => "Name and Phone are required"]);
            break;
        }
        if ($customer->isPhoneExists($data['phone'])) {
            http_response_code(409);
            echo json_encode(["error" => "Phone number already exists"]);
            break;
        }
        $new_id = $customer->create($data);
        if ($new_id) {
            http_response_code(201);
            echo json_encode(["message" => "Customer created successfully", "id" => $new_id]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create customer"]);
        }
        break;

    /**
     * طلبات تحديث بيانات عميل حالي (PUT Method)
     * تتطلب إرسال الـ ID وتتحقق من عدم تعارض الهاتف مع عميل آخر
     */
    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['phone']) && $customer->isPhoneExists($data['phone'], $id)) {
            http_response_code(409);
            echo json_encode(["error" => "Phone number already exists"]);
            break;
        }
        if ($customer->update($id, $data)) {
            http_response_code(200);
            echo json_encode(["message" => "Customer updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to update customer"]);
        }
        break;

    /**
     * طلبات حذف عميل (DELETE Method)
     * تتطلب إرسال الـ ID لحذف السجل من قاعدة البيانات
     */
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            break;
        }
        if ($customer->delete($id)) {
            http_response_code(200);
            echo json_encode(["message" => "Customer deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to delete customer"]);
        }
        break;

    /**
     * الاستجابة عند استدعاء طرق HTTP غير مدعومة
     */
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method Not Allowed"]);
        break;
}