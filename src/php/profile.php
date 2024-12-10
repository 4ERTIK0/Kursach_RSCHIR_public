<?php
include 'db.php';
session_start();

// Проверка, если пользователь не авторизован, перенаправляем на страницу входа
if (!isset($_SESSION['user'])) {
    header("Location: login.html");
    exit;
}

$error_message = '';
$success_message = '';

// Если форма была отправлена, обрабатываем данные
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user']['id'];
    $username = $_POST['username'] ?? null;
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;

    // Загрузка фотографии профиля
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = "../uploads/"; // Путь к папке загрузки
        $target_file = $upload_dir . basename($_FILES["profile_picture"]["name"]);

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $user_id);
            $stmt->execute();
        } else {
            $error_message = "Ошибка загрузки изображения.";
        }
    }

    // Обновление имени пользователя
    if ($username) {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
    }

    // Обновление пароля
    if ($password) {
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password, $user_id);
        $stmt->execute();
    }

    if (empty($error_message)) {
        $success_message = "Данные успешно обновлены!";
    }
}

// Получаем информацию о пользователе
$user_id = $_SESSION['user']['id'];
$sql = "SELECT username, profile_picture, email, is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <style>

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
            right: 160px;
            width: 80px;
            height: 80px;
            background-image: url();
            background-repeat: no-repeat;
        }

        .box .square:nth-child(2) {
            top: 350px;
            left: 65px;
            width: 70px;
            height: 70px;
            z-index: 2;
            background-image: url();
        }

        .box .square:nth-child(3) {
            top: 210px;
            left: 22px;
            width: 40px;
            height: 40px;
            z-index: 2;
            background-image: url();
        
        }
        .box .square:nth-child(4) {
            top: 290px;
            right: 200px;
            width: 45px;
            height: 45px;
            background-image: url();
        }

        .box .square:nth-child(5) {
            top: -50px;
            left: 186px;
            width: 75px;
            height: 75px;
            z-index: 2;
            background-image: url();
        }
        .box .square:nth-child(6) {
            top: 350px;
            left: 240px;
            width: 65px;
            height: 65px;
            z-index: 2;
            background-image: url();
        } 

        .profile-body {
            margin: 30px auto 0;
            background-color: transparent;
            box-shadow: none;
            gap: 15px;
            height: auto;
            background: rgba(117, 101, 132, 0.264);
            backdrop-filter: blur(5px);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.216);
        }

        
      
    </style>
    
    <title>Профиль</title>
</head>
<body>
    <header>
        <img src="../images/coin.png" height="25px" width="25px">
        <div>
            <a href="search.php">Поиск</a>
            <a href="profile.php">Профиль</a>
            <!-- Добавляем ссылку на админскую панель, если пользователь администратор -->
            <?php if ($user_data['is_admin'] == 1): ?>
                <a href="admin_dashboard.php">Админская панель</a>
            <?php endif; ?>
            <?php if ($user_data['is_admin'] == 1): ?>
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
            <div class="square" style="--i: 4"></div>
            <div class="square" style="--i: 5"></div>
        </div>
        <?php if (!empty($user_data['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($user_data['profile_picture']) ?>?t=<?= time() ?>" alt="Фото профиля">
        <?php endif; ?>

        <?php if (!empty($user_data['username'])): ?>
            <p><?= htmlspecialchars($user_data['username']) ?></p>
        <?php endif; ?>

        <p>Почта: <?= htmlspecialchars($user_data['email']) ?></p>

        <?php if ($error_message): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Введите новое имя">
            <input type="password" name="password" placeholder="Введите новый пароль">
            <input type="file" name="profile_picture">
            <div class="buttons">
                <button type="submit">Обновить</button>
                <button type="button" onclick="window.location.href='logout.php'">Выйти</button>
            </div> 
        </form>
    </div>
</body>
</html>
