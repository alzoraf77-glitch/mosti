<?php
session_start();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // تهيئة السلة إن لم تكن موجودة
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // إضافة أو زيادة الكمية
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
}

// العودة إلى الصفحة السابقة أو الرئيسية
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '02_show_products.php'));
exit;
?>