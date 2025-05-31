<?php
session_start();
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (
        empty($_POST['login']) ||
        empty($_POST['password'])
    ) {
        $message = "Заполните все поля";
    } else {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("
        SELECT * FROM users WHERE user_login = :user_login");
        $stmt->execute(['user_login' => $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['user_password'])) {
            $_SESSION["id"] = $user['user_id'];
            $_SESSION["auth"] = true;
            $_SESSION["role_id"] = $user['role_id'];
            if ($user["role_id"] == 1) {
                header('Location: admin.php');
            } else {
                header('Location: orders.php');
            }
            exit();

        } else {
            $message = "Неверный логин или пароль";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="bootstrap.min.css">
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
                            Вход
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message))
                            echo "<div class='alert alert-danger'>$message</div>" ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="login" class="form-label">Логин</label>
                                    <input type="text" class="form-control" name="login" placeholder="example" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Пароль</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Войти</button>
                            </form>
                            <p class="mt-3 text-start">
                                Нет аккаунта? <a href="reg.php">Регистрация</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>

    </html>
