<?php
include 'db.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;  // Если выбрано, то админ

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Некорректный email!";
    } else {
        // Проверка на существующего пользователя
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Пользователь с таким email уже существует!";
        } else {
            // Хешируем пароль перед сохранением
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO users (email, password, is_admin) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $email, $hashed_password, $is_admin);

            if ($stmt->execute()) {
                $success_message = "Регистрация прошла успешно!";
                header("Location: login.php");
                exit;
            } else {
                $error_message = "Ошибка регистрации: " . $conn->error;
            }
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0 5px;
            border: none; /* Убираем рамку */
            outline: none; /* Убираем фокус */
            cursor: pointer;
        }

        input[type="checkbox"]:focus {
            outline: none; /* Убираем зеленую рамку при фокусе */
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
            right: 23px;
            width: 100px;
            height: 100px;
            background-image: url();
            background-repeat: no-repeat;
        }

        .box .square:nth-child(2) {
            top: 320px;
            left: 45px;
            width: 70px;
            height: 70px;
            z-index: 2;
            background-image: url();
        }

        .box .square:nth-child(3) {
            top: 190px;
            left: 22px;
            width: 40px;
            height: 40px;
            z-index: 2;
            background-image: url();
        
        }
        .box .square:nth-child(4) {
            top: 280px;
            right: 190px;
            width: 95px;
            height: 95px;
            background-image: url();
        }

        .box .square:nth-child(5) {
            top: -80px;
            left: 165px;
            width: 80px;
            height: 80px;
            z-index: 2;
            background-image: url();
        }
        .box .square:nth-child(6) {
            top: 150px;
            left: 200px;
            width: 50px;
            height: 50px;
            z-index: 2;
            background-image: url();
        } 

        
              

    </style>
    <title>Регистрация</title>
</head>
<body>
    <div class="register-body">
        <div class="box">
            <div class="square" style="--i: 0"></div>
            <div class="square" style="--i: 1"></div>
            <div class="square" style="--i: 2"></div>
            <div class="square" style="--i: 3"></div>
            <div class="square" style="--i: 4"></div>
            <div class="square" style="--i: 5"></div>
        </div>
        <h1>Регистрация</h1>
        <?php if ($error_message): ?>
            <p style="color: red;"><?= $error_message ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p style="color: green;"><?= $success_message ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <input type="email" name="email" required placeholder="Введите почту">
            <input type="password" name="password" required placeholder="Введите пароль">
            <label>
                <input type="checkbox" name="is_admin"> Сделать администратором
            </label>
            <div class="buttons">
                <button type="submit">Регестрировать</button>
                <button type="button" onclick="window.location.href='../index.html'">На главную</button>
            </div>
        </form>
    </div>
</body>
</html>