<?php
include 'db.php';
session_start();

// Проверка, если пользователь не авторизован или не является администратором, перенаправляем на страницу входа
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

$error_message = '';
$success_message = '';

// Обработка добавления магазина, товара и цены
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Добавление магазина
    if (isset($_POST['store_name']) && isset($_POST['store_url'])) {
        $store_name = $_POST['store_name'];
        $store_url = $_POST['store_url'];
        $sql = "INSERT INTO stores (name, url) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $store_name, $store_url);
        $stmt->execute();
        $success_message = "Магазин успешно добавлен!";
    }

    // Добавление товара
    if (isset($_POST['product_name']) && isset($_POST['product_description'])) {
        $product_name = $_POST['product_name'];
        $product_description = $_POST['product_description'];
        $sql = "INSERT INTO products (name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $product_name, $product_description);
        $stmt->execute();
        $success_message = "Товар успешно добавлен!";
    }

    // Добавление цены
    if (isset($_POST['price']) && isset($_POST['product_id']) && isset($_POST['store_id'])) {
        $price = $_POST['price'];
        $product_id = $_POST['product_id'];
        $store_id = $_POST['store_id'];
        $sql = "INSERT INTO prices (product_id, store_id, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iid", $product_id, $store_id, $price);
        $stmt->execute();
        $success_message = "Цена успешно добавлена!";
    }
}

// Получаем список магазинов и товаров для выпадающих списков
$stores_sql = "SELECT id, name FROM stores";
$stores_result = $conn->query($stores_sql);

$products_sql = "SELECT id, name FROM products";
$products_result = $conn->query($products_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
         .admin-dashboard {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 10px;
            background: rgba(64, 57, 71, 0.264);
            backdrop-filter: blur(5px);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.216);
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f0f8ff; /* Светло-синий фон */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-section h2 {
            margin-top: 0;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input, select, textarea, button {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background-color: #45a049;
        }

        p {
            text-align: center;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

    </style>
    <title>Админская панель</title>
</head>
<body>
    <header>
        <img src="../images/coin.png" height="25px" width="25px">
        <div>
            <a href="search.php">Поиск</a>
            <a href="profile.php">Профиль</a>
            <?php if ($_SESSION['user']['is_admin'] == 1): ?>
                <a href="admin_dashboard.php">Админская панель</a>
                <a href="admin_manage.php">Редактировать реестр</a>
            <?php endif; ?>
            <a href="logout.php">Выйти</a>
        </div>
    </header>

    <div class="admin-dashboard">
        <h1>Админская панель</h1>

        <?php if ($success_message): ?>
            <p style="color: green;"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <h2>Добавить магазин</h2>
        <form action="admin_dashboard.php" method="post">
            <input type="text" name="store_name" placeholder="Название магазина" required>
            <input type="url" name="store_url" placeholder="URL магазина" required>
            <button type="submit">Добавить</button>
        </form>

        <h2>Добавить товар</h2>
        <form action="admin_dashboard.php" method="post">
            <input type="text" name="product_name" placeholder="Название товара" required>
            <textarea name="product_description" placeholder="Описание товара"></textarea>
            <button type="submit">Добавить</button>
        </form>

        <h2>Добавить цену</h2>
        <form action="admin_dashboard.php" method="post">
            <select name="product_id" required>
                <option value="" disabled selected>Выберите товар</option>
                <?php while ($product = $products_result->fetch_assoc()): ?>
                    <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <select name="store_id" required>
                <option value="" disabled selected>Выберите магазин</option>
                <?php while ($store = $stores_result->fetch_assoc()): ?>
                    <option value="<?= $store['id'] ?>"><?= htmlspecialchars($store['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <input type="number" name="price" placeholder="Цена товара" step="0.01" required>
            <button type="submit">Добавить</button>
        </form>
    </div>
</body>
</html>
