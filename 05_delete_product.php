<?php

require 'config.php';

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: 02_show_products.php');
    exit;
}

$product_id = (int)$_GET['id'];

// جلب بيانات المنتج (الاسم ومسار الملف) قبل الحذف
$stmt = $pdo->prepare("SELECT id, name, image FROM products WHERE id = :id");
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch();

// إذا لم يوجد المنتج، نعيد التوجيه
if (!$product) {
    header('Location: 02_show_products.php');
    exit;
}

// --- حذف الملف المرفق من السيرفر (إن وجد) ---
if (!empty($product['image']) && file_exists(__DIR__ . '/' . $product['image'])) {
    // محاولة حذف الملف
    if (unlink(__DIR__ . '/' . $product['image'])) {
        // تم الحذف بنجاح (اختياري: يمكنك تسجيل ذلك في log)
    } else {
        // فشل حذف الملف (يمكنك تجاهل الخطأ أو تسجيله)
    }
}

// --- حذف السجل من قاعدة البيانات ---
$stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
$stmt->execute([':id' => $product_id]);

// التوجيه إلى صفحة العرض مع رسالة نجاح
header('Location: 02_show_products.php?deleted=1&name=' . urlencode($product['name']));
exit;

?>