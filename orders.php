<?php
session_start();
include "db.php";

if (empty($_SESSION['auth'])) {
    header('Location: index.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT * 
    FROM orders 
    LEFT JOIN statuses ON orders.status_id = statuses.status_id 
    WHERE user_id = :user_id
");
$stmt->execute(['user_id' => $_SESSION['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все заявки</title>
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

    <main class="container mt-3">
        <h2 class="text-center mb-3">Все заявки</h2>
        <div class="flex-column align-items-center row">
            <?php
            foreach ($orders as $order) {
                echo "
                <div class='card col-md-8 col-lg-6 mb-3'>
                    <div class='card-body'>
                        <ul class='list-unstyled'>
                            <li><b>Услуга:</b> {$order['order_service']}</li>
                            <li><b>Адрес:</b> {$order['order_address']}</li>
                            <li><b>Дата:</b> {$order['order_date_of']}</li>
                            <li><b>Время:</b> {$order['order_time']}</li>
                            <li><b>Тип оплаты:</b> {$order['order_payment_type']}</li>
                            <li><b>Статус:</b> {$order['status_name']}</li>";

                if (!empty($order['order_note'])) {
                    echo "<br><li><b>Замечание:</b> {$order['order_note']}</li>";
                }

                echo "
                        </ul>
                    </div>
                </div>";
            }
            ?>
        </div>


    </main>

</body>

</html>
