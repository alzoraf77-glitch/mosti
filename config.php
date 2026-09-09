<?php
// دالة مساعدة للهروب من النصوص (حماية من XSS)
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// ---- اتصال قاعدة البيانات ----
class Database {
    private $host = "localhost";
    private $db_name = "mini_store";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("خطأ في الاتصال: " . $e->getMessage());
        }
        return $this->conn;
    }
}

// ---- نموذج المنتج (ProductModel) ----
class ProductModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // جلب كل المنتجات مع اسم التصنيف
    public function getAll() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // جلب منتج واحد حسب ID
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // البحث عن منتج
    public function search($keyword) {
        $searchTerm = '%' . $keyword . '%';
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.name LIKE :keyword 
                  OR p.description LIKE :keyword 
                  OR c.name LIKE :keyword
                  ORDER BY p.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':keyword', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // جلب التصنيفات (للاستخدام في الإضافة والتعديل)
    public function getCategories() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>