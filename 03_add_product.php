<?php
require_once 'config.php';
$model = new ProductModel();
$categories = $model->getCategories();

$errors = [];
$success = '';
$name = $description = $price = $quantity = '';
$category_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '0');
    $category_id = (int)($_POST['category_id'] ?? 0);

    // تحقق
    if (empty($name)) $errors[] = 'اسم المنتج مطلوب.';
    if ($category_id <= 0) $errors[] = 'يجب اختيار تصنيف.';
    if (!is_numeric($price) || $price <= 0) $errors[] = 'السعر يجب أن يكون رقماً أكبر من صفر.';
    if (!ctype_digit($quantity)) $errors[] = 'الكمية يجب أن تكون عدداً صحيحاً.';

    // رفع الصورة
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = 'uploads/' . $new_name;
            } else {
                $errors[] = 'فشل رفع الصورة.';
            }
        } else {
            $errors[] = 'امتداد الصورة غير مسموح (jpg, png, gif, webp).';
        }
    }

    if (empty($errors)) {
        $pdo = (new Database())->getConnection();
        $sql = "INSERT INTO products (category_id, name, description, price, quantity, image) 
                VALUES (:category_id, :name, :description, :price, :quantity, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $category_id,
            ':name' => $name,
            ':description' => $description,
            ':price' => (float)$price,
            ':quantity' => (int)$quantity,
            ':image' => $image_path
        ]);
        $success = '✅ تمت إضافة المنتج بنجاح!';
        // تصفير الحقول
        $name = $description = $price = $quantity = '';
        $category_id = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة منتج جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>➕ إضافة منتج جديد</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?> <a href="02_show_products.php">عرض المنتجات</a></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">اسم المنتج *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= e($name) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">الوصف</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= e($description) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">السعر ($) *</label>
                    <input type="number" step="0.01" id="price" name="price" class="form-control" value="<?= e($price) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="quantity" class="form-label">الكمية *</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" value="<?= e($quantity) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="category_id" class="form-label">التصنيف *</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="0">اختر التصنيف</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">صورة المنتج</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-success">💾 حفظ المنتج</button>
                <a href="02_show_products.php" class="btn btn-secondary">إلغاء</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>