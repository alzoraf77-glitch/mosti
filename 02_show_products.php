<?php
session_start();
require_once 'config.php';

$model = new ProductModel();
$products = $model->getAll();

// حساب عدد المنتجات في السلة
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجر الملابس - الرئيسية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .product-img { height: 200px; object-fit: cover; }
        .card { transition: 0.3s; }
        .card:hover { transform: scale(1.02); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .cart-badge { position: relative; top: -10px; right: 5px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="02_show_products.php">👕 متجر الملابس</a>
        <div>
            <a href="cart.php" class="btn btn-outline-light position-relative">
                🛒 سلة التسوق
                <?php if ($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $cart_count ?>
                    </span>
                <?php endif; ?>
            </a>
            <!-- أزرار الإدارة (للمسؤول) -->
            <a href="03_add_product.php" class="btn btn-success ms-2">➕ إضافة منتج</a>
            <a href="04_search_products.php" class="btn btn-info ms-2">🔍 بحث</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">🛍️ أحدث التشكيلات</h2>
    <div class="row">
        <?php if (empty($products)): ?>
            <div class="alert alert-warning">لا توجد منتجات حالياً.</div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card h-100">
                    <img src="<?= !empty($product['image']) ? e($product['image']) : 'uploads/default.jpg' ?>" 
                         class="card-img-top product-img" alt="<?= e($product['name']) ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?= e($product['name']) ?></h5>
                        <p class="card-text text-truncate"><?= e($product['description']) ?></p>
                        <span class="badge bg-secondary"><?= e($product['category_name'] ?? 'بدون تصنيف') ?></span>
                        <h6 class="text-success mt-2">$<?= number_format($product['price'], 2) ?></h6>
                        <div class="d-grid gap-2">
                            <a href="add_to_cart.php?id=<?= $product['id'] ?>" class="btn btn-success btn-sm">🛒 أضف للسلة</a>
                            <a href="07_edit_product.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">✏️ تعديل</a>
                            <a href="06_delete_product_interface.php?id=<?= $product['id'] ?>" class="btn btn-danger btn-sm">🗑️ حذف</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>