<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (
        empty($_POST['fio']) ||
        empty($_POST['tel']) ||
        empty($_POST['email']) ||
        empty($_POST['login']) ||
        empty($_POST['password'])
    ) {
        $message = "Заполните все поля";
    } else {
        $fio = $_POST['fio'];
        $tel = $_POST['tel'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (user_fio, user_phone, user_email, user_login, user_password) VALUES (:user_fio, :user_phone, :user_email, :user_login, :user_password)");

        if (
            $stmt->execute([
                "user_fio" => $fio,
                "user_phone" => $tel,
                "user_email" => $email,
                "user_login" => $login,
                "user_password" => $password
            ])
        ) {
            $_SESSION["id"] = $pdo->lastInsertId();
            $_SESSION["auth"] = true;
            header('Location: orders.php');
            exit();
        } else {
            $message = "Ошибка регистрации";
        }
    }

}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap.min.css">
    <title>Регистрация</title>
</head>

<body>
    <header class="bg-dark text-white text-center p-3">
        <h2>Портал клининговых услуг "Мой не сам"</h2>
    </header>

    <main class="container mt-5">
        <div class="justify-content-center row">

            <div class="col-lg-6 col-xl-5">

                <div class="card">
                    <div class="card-header bg-secondary text-white text-center">
                        <h3>
                            Регистрация
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message))
                            echo "<div class='alert alert-danger'>$message</div>" ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="fio" class="form-label">ФИО</label>
                                    <input type="text" class="form-control" name="fio" placeholder="Иванов И.И." required>
                                </div>
                                <div class="mb-3">
                                    <label for="tel" class="form-label">Телефон</label>
                                    <input type="tel" class="form-control" name="tel" required
                                        placeholder="8(999)999-99-99">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="example@gmail.com"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="login" class="form-label">Логин</label>
                                    <input type="text" class="form-control" name="login" placeholder="example" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                            </form>
                            <p class="mt-3 text-start">
                                Есть аккаунт? <a href="index.php">Войти</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>

    </html>
