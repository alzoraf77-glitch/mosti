<?php
session_start();
require_once 'config.php';

$model = new ProductModel();
$cartItems = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $quantity) {
        $product = $model->getById($id);
        if ($product) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $total += $product['subtotal'];
            $cartItems[] = $product;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>سلة المشتريات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="02_show_products.php">⬅ العودة للمتجر</a>
    </div>
</nav>
<div class="container mt-4">
    <h2>🛒 سلة المشتريات</h2>

    <?php if (empty($cartItems)): ?>
        <div class="alert alert-info">سلتك فارغة! <a href="02_show_products.php">تسوق الآن</a></div>
    <?php else: ?>
        <form action="update_cart.php" method="POST">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>المجموع الفرعي</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td><?= e($item['name']) ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input type="number" name="quantities[<?= $item['id'] ?>]" 
                                   value="<?= $item['quantity'] ?>" min="1" class="form-control w-50">
                        </td>
                        <td>$<?= number_format($item['subtotal'], 2) ?></td>
                        <td>
                            <a href="remove_from_cart.php?id=<?= $item['id'] ?>" 
                               class="btn btn-danger btn-sm" onclick="return confirm('احذف المنتج؟')">✖</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">الإجمالي الكلي:</th>
                        <th colspan="2">$<?= number_format($total, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
            <button type="submit" class="btn btn-warning">🔄 تحديث الكميات</button>
            <a href="02_show_products.php" class="btn btn-primary">مواصلة التسوق</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>