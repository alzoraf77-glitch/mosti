
<?php

require 'config.php';

$errors = [];
$success = '';

$name = '';
$description = '';
$price = '';
$quantity = '0';
$category_id = 0;

$categories = $pdo->query(
    'SELECT id, name FROM categories ORDER BY name ASC'
)->fetchAll();



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');

    $description = trim ($_POST['description'] ?? '');
    

    $price = trim ($_POST['price'] ?? '');
    

    $quantity = trim ($_POST['quantity'] ?? '0');
    

    $category_id = (int) ($_POST['category_id'] ?? 0);
  
    if ($name === '') {

        $errors[] = 'اسم المنتج مطلوب.';

    } elseif (mb_strlen($name) < 3) {

        $errors[] = 'اسم المنتج يجب ألا يقل عن 3 أحرف.';
    }


    if ($category_id <= 0) {

        $errors[] = 'يجب اختيار تصنيف المنتج.';
    }


    if (
        $price === ''|| !is_numeric($price) || (float)$price <= 0
    ) {

        $errors[] = 'السعر يجب أن يكون رقماً أكبر من صفر.';
    }


    if (
        $quantity === '' || !ctype_digit((string)$quantity)
    ) {

        $errors[] = 'الكمية يجب أن تكون عدداً صحيحاً غير سالب.';
    }

    if (empty($errors)) {

        $sql = "INSERT INTO products(category_id,name,description,price,quantity )
            VALUES(:category_id,:name,:description,:price,:quantity)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':category_id' => $category_id,
            ':name' => $name,
            ':description' => $description,
            ':price' => (float)$price,
            ':quantity' => (int)$quantity
        ]);

        $success = 'تمت إضافة المنتج بنجاح';
        $name = '';
        $description = '';
        $price = '';
        $quantity = '0';
        $category_id = 0;
    }
}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <title>إضافة منتج</title>

</head>

<body>

    <h1>إضافة منتج جديد</h1>


    <?php if (!empty($errors)): ?>

        <section class="alert">

            <h2>يرجى تصحيح الأخطاء التالية:</h2>

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= e($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </section>

    <?php endif; ?>


    <?php if ($success): ?>

        <section class="success">

            <strong>
                <?= e($success) ?>
            </strong>

            <br>

            <a href="02_show_products.php">
                عرض المنتجات
            </a>

        </section>

    <?php endif; ?>


    <form method="POST" action="">

        <div>

            <label for="name">
                اسم المنتج:
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="<?= e($name) ?>"
            >

        </div>


        <br>


        <div>

            <label for="description">
                الوصف:
            </label>

            <textarea
                id="description"
                name="description"
            ><?= e($description) ?></textarea>

        </div>


        <br>


        <div>

            <label for="price">
                السعر:
            </label>

            <input
                type="text"
                id="price"
                name="price"
                value="<?= e($price) ?>"
            >

        </div>


<br>


        <div>

            <label for="quantity">
                الكمية:
            </label>

            <input
                type="number"
                id="quantity"
                name="quantity"
                min="0"
                value="<?= e($quantity) ?>"
            >

        </div>


        <br>


        <div>

            <label for="category_id">
                التصنيف:
            </label>

            <select
                id="category_id"
                name="category_id"
            >

                <option value="0">
                    اختر التصنيف
                </option>


                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?= e($category['id']) ?>"
                        <?= (
                            (int)$category['id']
                            ===
                            (int)$category_id
                        ) ? 'selected' : '' ?>
                    >

                        <?= e($category['name']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <br>


        <button type="submit">
            إضافة المنتج
        </button>

    </form>

</body>

</html>