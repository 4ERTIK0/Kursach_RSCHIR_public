<?php
include 'db.php';
session_start();

// Проверка авторизации
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit;
}

$query = $_GET['query'] ?? '';
$results = [];
$catalog = [];
$best_offer = null;

// Получение полного каталога товаров
$sql_catalog = "SELECT id, name FROM products ORDER BY name";
$result_catalog = $conn->query($sql_catalog);
while ($row = $result_catalog->fetch_assoc()) {
    $catalog[] = $row;
}

// Выполнение поиска
if ($query) {
    $sql = "
        SELECT 
            products.name AS product_name, 
            products.description AS product_description, 
            stores.name AS store_name, 
            stores.url AS store_url, 
            prices.price 
        FROM prices 
        JOIN products ON prices.product_id = products.id 
        JOIN stores ON prices.store_id = stores.id 
        WHERE products.name LIKE ?
    ";
    $stmt = $conn->prepare($sql);
    $likeQuery = "%" . $query . "%";
    $stmt->bind_param("s", $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    // Выбор самого выгодного предложения
    if (!empty($results)) {
        $best_offer = $results[0];
        foreach ($results as $result) {
            if ($result['price'] < $best_offer['price']) {
                $best_offer = $result;
            }
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Поиск товаров</title>
    <style>
        
        .highlight {
            background-color: #f5a623; /* Золотистый фон */
            color: black; /* Контрастный текст */
            padding: 10px;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #636090;
        }

        table tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }

        table tr:nth-child(odd) {
            background-color: rgba(0, 0, 0, 0.3);
        }
        .catalog-dropdown {
            position: relative;
            display: inline-block;
            text-align: center;
        }

        .catalog-button {
            background-color: #636090; /* Цвет кнопки */
            color: white;
            font-size: 18px; /* Размер шрифта */
            padding: 10px 20px; /* Внутренние отступы */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-block; /* Устанавливаем размер кнопки по содержимому */
            white-space: nowrap; /* Запрещаем перенос текста */
            line-height: 1.2; /* Регулируем высоту строки, чтобы текст располагался по центру */
            text-align: center; /* Выравнивание текста */
            overflow: hidden; /* Исключаем переполнение */
            box-sizing: border-box; /* Учитываем padding в размерах */
        }

        .catalog-button:hover {
            background-color: #83629f; /* Цвет при наведении */
        }

        .dropdown-content {
            display: none; /* Изначально список скрыт */
            position: absolute;
            background-color: rgba(0, 0, 0, 0.9); /* Фон выпадающего меню */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            border-radius: 5px;
            padding: 10px;
            z-index: 1;
            width: 150px; /* Ширина выпадающего списка */
        }

        .dropdown-content form {
            margin: 5px 0;
        }

        .dropdown-content .dropdown-item {
            background-color: transparent;
            color: white;
            font-size: 16px;
            border: none;
            text-align: left;
            width: 100%;
            padding: 8px 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }

        .dropdown-content .dropdown-item:hover {
            background-color: #83629f;
        }

/* Показать меню при наведении на кнопку */
        .catalog-dropdown:hover .dropdown-content {
            display: block;
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
            top: 400px;
            right: 260px;
            width: 140px;
            height: 140px;
            background-image: url();
        }

        .profile-body {
            margin: 30px auto 0;
            background-color: transparent;
            box-shadow: none;
            gap: 15px;
            height: auto;
            margin-top: 100px;

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
        <h1>Анализ цен интернет-магазинов</h1>

        <!-- Форма поиска -->
        <form method="GET" action="search.php">
            <input type="text" name="query" value="<?= htmlspecialchars($query) ?>" placeholder="Введите название товара">
            <button type="submit">Искать</button>
        </form>

        <!-- Каталог товаров -->
        <div class="catalog-dropdown">
            <button class="catalog-button">Каталог</button>
            <div class="dropdown-content">
                <?php foreach ($catalog as $item): ?>
                    <form method="GET" action="search.php" style="margin: 0;">
                        <button type="submit" name="query" value="<?= htmlspecialchars($item['name']) ?>" class="dropdown-item">
                            <?= htmlspecialchars($item['name']) ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- Результаты поиска -->
        <div>
            <?php if ($query && $best_offer): ?>
                <div class="highlight">
                    <h2>Самое выгодное предложение</h2>
                    <p><strong>Товар:</strong> <?= htmlspecialchars($best_offer['product_name']) ?></p>
                    <p><strong>Описание:</strong> <?= htmlspecialchars($best_offer['product_description']) ?></p>
                    <p><strong>Магазин:</strong> <?= htmlspecialchars($best_offer['store_name']) ?></p>
                    <p><strong>Цена:</strong> <?= htmlspecialchars($best_offer['price']) ?> руб.</p>
                    <p><a href="<?= htmlspecialchars($best_offer['store_url']) ?>" target="_blank">Перейти в магазин</a></p>
                </div>
            <?php elseif ($query && empty($results)): ?>
                <p>Ничего не найдено для запроса: <?= htmlspecialchars($query) ?>.</p>
            <?php endif; ?>

            <div style="max-height: 285px; overflow-y: auto; margin-top: 20px; padding: 10px; border-radius: 5px;">
                <!-- Результаты поиска -->
                <?php if (!empty($results)): ?>
                    <h2>Результаты поиска</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Описание</th>
                                <th>Магазин</th>
                                <th>Цена</th>
                                <th>Ссылка</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= htmlspecialchars($row['product_description']) ?></td>
                                    <td><?= htmlspecialchars($row['store_name']) ?></td>
                                    <td><?= htmlspecialchars($row['price']) ?> руб.</td>
                                    <td><a href="<?= htmlspecialchars($row['store_url']) ?>" target="_blank">Перейти</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

    </div>
</body>
</html>


<?php
$conn->close();
?>
