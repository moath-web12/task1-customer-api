# Task 1 - Customer Management REST API

API مبني بـ PHP و MySQL لإدارة بيانات العملاء بأسلوب RESTful.

## Setup
1. قم باستيراد `database.sql` في قاعدة البيانات MySQL.
2. عدّل بيانات الاتصال في `config/database.php`.

## Endpoints

* **إنشاء عميل جديد (Create Customer):**
  `POST http://localhost/task1-customer-api/api/customers.php`

* **عرض جميع العملاء (Get All Customers):**
  `GET http://localhost/task1-customer-api/api/customers.php`

* **عرض عميل محدد بالمعرّف (Get Single Customer):**
  `GET http://localhost/task1-customer-api/api/customers.php/1`

* **تعديل بيانات عميل (Update Customer):**
  `PUT http://localhost/task1-customer-api/api/customers.php?id=1`

* **حذف عميل (Delete Customer):**
  `DELETE http://localhost/task1-customer-api/api/customers.php/1`

* **البحث برقم الهاتف أو الاسم أو الإيميل (Search Customer):**
  `GET http://localhost/task1-customer-api/api/customers.php?phone=0501388396`