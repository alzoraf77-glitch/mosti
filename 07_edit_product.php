<?php

require 'config.php';

$errors = [];
$success = '';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: 02_show_products.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: 02_show_products.php');
    exit;
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();

$name = $product['name'];
$description = $product['description'];
$price = $product['price'];
$quantity = $product['quantity'];
$category_id = $product['category_id'];
$current_file = $product['image']; // قد تكون null

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '0');
    $category_id = (int)($_POST['category_id'] ?? 0);

    // نفس التحقق
    if ($name === '') {
        $errors[] = 'اسم الدواء مطلوب.';
    } elseif (mb_strlen($name) < 3) {
        $errors[] = 'اسم الدواء يجب ألا يقل عن 3 أحرف.';
    }

    if ($category_id <= 0) {
        $errors[] = 'يجب اختيار تصنيف الدواء.';
    }

    if ($price === '' || !is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'السعر يجب أن يكون رقماً أكبر من صفر.';
    }

    if ($quantity === '' || !ctype_digit((string)$quantity)) {
        $errors[] = 'الكمية يجب أن تكون عدداً صحيحاً غير سالب.';
    }

    // معالجة رفع الملف الجديد
    $uploaded_file = $current_file; // الاحتفاظ بالملف القديم افتراضياً

    if (empty($errors) && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $max_size = 5 * 1024 * 1024;
        if ($_FILES['file']['size'] > $max_size) {
            $errors[] = 'حجم الملف يتجاوز الحد المسموح به (5 ميجابايت).';
        } else {
            $file_name = $_FILES['file']['name'];
            $file_tmp = $_FILES['file']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // (اختياري) قيود على الامتدادات
            // if (!in_array($file_ext, ['jpg', 'png', 'pdf', ...])) { ... }

            if (empty($errors)) {
                $new_name = uniqid() . '.' . $file_ext;
                $upload_dir = __DIR__ . '/uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $destination = $upload_dir . $new_name;
                if (move_uploaded_file($file_tmp, $destination)) {
                    // حذف الملف القديم إن وجد
                    if ($current_file && file_exists(__DIR__ . '/' . $current_file)) {
                        unlink(__DIR__ . '/' . $current_file);
                    }
                    $uploaded_file = 'uploads/' . $new_name;
                } else {
                    $errors[] = 'فشل رفع الملف، حاول مرة أخرى.';
                }
            }
        }
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'حدث خطأ أثناء رفع الملف: ' . $_FILES['file']['error'];
    }

    // معالجة طلب حذف الملف الحالي
    if (isset($_POST['remove_file']) && $_POST['remove_file'] === '1') {
        if ($current_file && file_exists(__DIR__ . '/' . $current_file)) {
            unlink(__DIR__ . '/' . $current_file);
        }
        $uploaded_file = null;
    }

    if (empty($errors)) {
        $sql = "UPDATE products 
                SET category_id = :category_id,
                    name = :name,
                    description = :description,
                    price = :price,
                    quantity = :quantity,
                    image = :image
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $category_id,
            ':name' => $name,
            ':description' => $description,
            ':price' => (float)$price,
            ':quantity' => (int)$quantity,
            ':image' => $uploaded_file,
            ':id' => $product_id
        ]);

        $success = 'تم تحديث المنتج بنجاح';
        $current_file = $uploaded_file; // تحديث المتغير
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل منتج - صيدلية</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <header class="page-title">
        <h1> تعديل المنتج</h1>
        <p>قم بتحديث بيانات المنتج</p>
    </header>

    <nav class="nav">
        <a href="index.php"> الرئيسية</a>
        <a href="01_test_connection.php"> اختبار الاتصال</a>
        <a href="02_show_products.php"> عرض الأدوية</a>
        <a href="03_add_product.php"> إضافة دواء</a>
        <a href="04_search_products.php"> بحث الأدوية</a>
    </nav>

    <?php if (!empty($errors)): ?>
        <section class="alert">
            <h2> يرجى تصحيح الأخطاء التالية:</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <?php if ($success): ?>
        <section class="success">
            <h2><?= e($success) ?></h2>
            <br>
            <a href="02_show_products.php" style="display: inline-block; background: #0f766e; color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;"> العودة إلى قائمة الأدوية</a>
        </section>
    <?php endif; ?>

    <section class="card">
        <form method="POST" action="" enctype="multipart/form-data" class="form">
            <div>
                <label for="name">اسم الدواء: <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="<?= e($name) ?>" required>
            </div>

            <div>
                <label for="description">الوصف:</label>
                <textarea id="description" name="description" rows="4"><?= e($description) ?></textarea>
            </div>

            <div>
                <label for="price">السعر (ريال): <span class="required">*</span></label>
                <input type="number" id="price" name="price" value="<?= e($price) ?>" step="0.01" min="0.01" required>
            </div>

            <div>
                <label for="quantity">الكمية المتوفرة: <span class="required">*</span></label>
                <input type="number" id="quantity" name="quantity" min="0" value="<?= e($quantity) ?>" required>
            </div>

            <div>
                <label for="category_id">التصنيف: <span class="required">*</span></label>
                <select id="category_id" name="category_id" required>
                    <option value="0"> اختر التصنيف </option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category['id']) ?>" <?= ((int)$category['id'] === (int)$category_id) ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- عرض الملف الحالي إن وجد -->
            <div>
                <label>الملف الحالي:</label>
                <?php if ($current_file && file_exists(__DIR__ . '/' . $current_file)): ?>
                    <div style="margin: 10px 0;">
                        <?php
                        $ext = strtolower(pathinfo($current_file, PATHINFO_EXTENSION));
                        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
                        if (in_array($ext, $image_exts)) {
                            echo '<img src="' . e($current_file) . '" alt="ملف" style="max-width: 150px; max-height: 150px; border-radius: 8px; border: 1px solid #ddd;">';
                        } else {
                            // عرض اسم الملف مع رابط تحميل
                            echo '<a href="' . e($current_file) . '" target="_blank">' . e(basename($current_file)) . '</a>';
                        }
                        ?>
                    </div>
                    <div style="margin: 5px 0;">
                        <label>
                            <input type="checkbox" name="remove_file" value="1"> حذف الملف الحالي
                        </label>
                    </div>
                <?php else: ?>
                    <p style="color: #6b7280;">لا يوجد ملف مرفق حالياً</p>
                <?php endif; ?>
            </div>

            <!-- رفع ملف جديد -->
            <div>
                <label for="file">تغيير الملف (اختياري):</label>
                <input type="file" id="file" name="file">
                <small style="color: #6b7280;">الحد الأقصى للحجم 5 ميجابايت</small>
            </div>

            <button type="submit"> تحديث المنتج</button>
            <a href="02_show_products.php" style="background: #6b7280; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 8px; display: inline-block;"> إلغاء</a>
        </form>
    </section>
</div>
</body>
</html>