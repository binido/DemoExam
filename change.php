<?php
session_start();
include "db.php";

if (empty($_SESSION['auth']) || $_SESSION['role_id'] != 1) {
    header('Location: orders.php');
    exit();
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    if (
        empty($_POST['status_id']) ||
        empty($_POST['order_id'])
    ) {
        $message = "Заполните все поля";
    } else {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status_id = :status_id, order_note = :order_note 
            WHERE order_id = :order_id 
        ");
        if (
            $stmt->execute([
                'status_id' => $_POST['status_id'],
                'order_note' => $_POST['order_note'],
                'order_id' => $_POST['order_id']
            ])
        ) {
            header('Location: admin.php');
            exit();
        } else {
            $message = "Ошибка при изменении заявки";
        }
    }

}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Изменить заявку</title>
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body>
    <header class="bg-dark text-white text-center p-3">
        <h2>Портал клининговых услуг "Мой не сам"</h2>
        <h4>Админ-панель</h4>
    </header>
    <nav class="navblock m-0">
        <ul class="list-unstyled d-flex justify-content-end nav bg-secondary p-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="admin.php">Все заявки</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="change.php">Изменить заявку</a>
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
                            Изменение заявки
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message))
                            echo "<div class='alert alert-danger'>$message</div>" ?>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="order_id" class="form-label">ID заявки</label>
                                    <input type="number" class="form-control" name="order_id"
                                        placeholder="Введите ID заявки" required>
                                </div>
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">Статус заявки</label>
                                    <select class="form-select" name="status_id" required>
                                        <option value="1">Новая</option>
                                        <option value="2">Услуга оказана</option>
                                        <option value="3">Услуга отклонена</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="order_note" class="form-label">Примечание</label>
                                    <textarea class="form-control" name="order_note" rows="3"
                                        placeholder="Введите примечание"></textarea>
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
