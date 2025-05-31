<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['auth'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        empty($_POST['order_service']) ||
        empty($_POST['order_address']) ||
        empty($_POST['order_date_of']) ||
        empty($_POST['order_time']) ||
        empty($_POST['order_payment_type'])
    ) {
        $message = "Заполните все поля";
    } else {
        $service = $_POST['order_service'];
        $address = $_POST['order_address'];
        $date_of = $_POST['order_date_of'];
        $time = $_POST['order_time'];
        $payment_type = $_POST['order_payment_type'];

        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_service, order_address, order_date_of, order_time, order_payment_type) 
            VALUES (:user_id, :order_service, :order_address, :order_date_of, :order_time, :order_payment_type)
        ");

        if (
            $stmt->execute([
                "user_id" => $_SESSION["id"],
                "order_service" => $service,
                "order_address" => $address,
                "order_date_of" => $date_of,
                "order_time" => $time,
                "order_payment_type" => $payment_type
            ])
        ) {
            header('Location: orders.php');
            exit();
        } else {
            $message = "Ошибка создания заявки";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body>
    <header class="bg-dark text-white text-center p-3">
        <h2>Портал клининговых услуг "Мой не сам"</h2>
    </header>
    <nav class="navblock m-0">
        <ul class="list-unstyled d-flex justify-content-end nav bg-secondary p-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="orders.php">Мои заявки</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="create.php">Создать заявку</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="logout.php">Выход</a>
            </li>
        </ul>
    </nav>

    <main class="container mt-5">
        <div class="justify-content-center row">

            <div class="col-lg-6 col-xl-5">

                <div class="card">
                    <div class="card-header bg-secondary text-white text-center">
                        <h3>
                            Создание заявки
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message))
                            echo "<div class='alert alert-danger'>$message</div>" ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="order_service" class="form-label">Услуга</label>
                                    <select class="form-select" name="order_service" required>
                                        <option disabled selected>Не выбрано</option>
                                        <option value="Уборка квартиры">Уборка квартиры</option>
                                        <option value="Уборка офиса">Уборка офиса</option>
                                        <option value="Генеральная уборка">Генеральная уборка</option>
                                        <option value="Химчистка">Химчистка</option>
                                        <option value="Мытье окон">Мытье окон</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="order_address" class="form-label">Адрес</label>
                                    <input type="text" class="form-control" name="order_address" required>
                                </div>
                                <div class="mb-3">
                                    <label for="order_date_of" class="form-label">Дата</label>
                                    <input type="date" class="form-control" name="order_date_of" required>
                                </div>
                                <div class="mb-3">
                                    <label for="order_time" class="form-label">Время</label>
                                    <input type="time" class="form-control" name="order_time" placeholder="example"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="order_payment_type" class="form-label">Тип оплаты</label>
                                    <select class="form-select" name="order_payment_type" required>
                                        <option value="Наличные">Наличные</option>
                                        <option value="Банковская карта">Банковская карта</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Создать</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>


    </body>

    </html>
