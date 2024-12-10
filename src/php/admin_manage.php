<?php
// Подключение к базе данных
include 'db.php';
session_start();

// Проверка, если пользователь не авторизован или не является администратором
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: login.html");
    exit;
}

// Функция удаления пользователя
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_manage.php");
    exit;
}

// Функция удаления товара и связанных с ним данных
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    
    // Удаляем все связанные записи в таблице цен
    $sql_price = "DELETE FROM prices WHERE product_id = ?";
    $stmt_price = $conn->prepare($sql_price);
    $stmt_price->bind_param("i", $product_id);
    $stmt_price->execute();
    
    // Удаляем товар
    $sql_product = "DELETE FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($sql_product);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();

    // Удаляем магазины, которые больше не имеют товаров
    $sql_store = "DELETE FROM stores WHERE id NOT IN (SELECT store_id FROM prices)";
    $stmt_store = $conn->prepare($sql_store);
    $stmt_store->execute();

    $stmt_price->close();
    $stmt_product->close();
    $stmt_store->close();
    
    header("Location: admin_manage.php");
    exit;
}

// Получаем список пользователей
$sql_users = "SELECT id, username, email FROM users";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->execute();
$users_result = $stmt_users->get_result();  // Здесь мы сохраняем результат в переменную
$stmt_users->close();

// Получаем список товаров
$sql_products = "SELECT id, name FROM products";
$stmt_products = $conn->prepare($sql_products);
$stmt_products->execute();
$products_result = $stmt_products->get_result();
$stmt_products->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Управление пользователями и данными</title>
    <style>
        /* Стили для таблиц */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f0f8ff; /* Светло-синий фон */
            border: 1px solid #ddd;
            color: black; /* Черный цвет шрифта */
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f0f8ff; /* Светло-зеленый фон */
            border: 1px solid #ddd;
            color: black; /* Черный цвет шрифта */
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #e6e6e6; /* Светлый цвет для заголовков */
        }

        .danger-link {
            color: red;
            text-decoration: none;
        }

        .danger-link:hover {
            text-decoration: underline;
        }

        /* Дополнительный стиль для разделителей */
        h2 {
            color: #333;
            font-size: 1.5em;
            margin-top: 30px;
        }
        .box {
            position: relative;
        }
        .box .square {
            position: absolute;
            backdrop-filter: blur(5px);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 80px;
            animation: animate 10s linear infinite;
            animation-delay: calc(-1s * var(--i));
        }
        @keyframes animate {
            0%,
            100% {
                transform: translateY(-52px);
            }
            50% {
                transform: translateY(52px);
            }
        }
        .box .square:nth-child(1) {
            top: -100px;
            right: 185px;
            width: 100px;
            height: 100px;
            background-image: url();
            background-repeat: no-repeat;
        }

        .box .square:nth-child(2) {
            top: 400px;
            left: 310px;
            width: 70px;
            height: 70px;
            z-index: 2;
            background-image: url();
        }

        .box .square:nth-child(3) {
            top: 120px;
            left: 210px;
            width: 40px;
            height: 40px;
            z-index: 2;
            background-image: url();
        
        }

        .box .square:nth-child(4) {
            top: 500px;
            right: 260px;
            width: 140px;
            height: 140px;
            background-image: url();
        }

        .table-container {
            max-height: 200px; /* Максимальная высота таблицы */
            overflow-y: auto; /* Включение вертикальной прокрутки */
            overflow-x: hidden; /* Отключение горизонтальной прокрутки */
            margin-bottom: 20px; /* Отступ снизу */
        }
              
    </style>
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

    <div class="profile-body">
        <div class="box">
            <div class="square" style="--i: 0"></div>
            <div class="square" style="--i: 1"></div>
            <div class="square" style="--i: 2"></div>
            <div class="square" style="--i: 3"></div>
        </div>
        <h1>Управление пользователями и данными</h1>

        <!-- Таблица пользователей -->
        <h2>Существующие пользователи</h2>
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Имя пользователя</th>
                        <th>Email</th>
                        <th>Удалить</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username'] ?? 'Не указано') ?></td>
                            <td><?= htmlspecialchars($user['email'] ?? 'Не указан') ?></td>
                            <td>
                                <a class="danger-link" href="?delete_user=<?= $user['id'] ?>" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?');">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h2>Управление товарами</h2>
        <div class="table-container">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th>Удалить</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name'] ?? 'Не указан') ?></td>
                            <td>
                                <a class="danger-link" href="?delete_product=<?= $product['id'] ?>" onclick="return confirm('Вы уверены, что хотите удалить этот товар и все связанные с ним данные?');">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
    </div>
</body>
</html>
