# Полный гайд по созданию приложения клининговых услуг на PHP

## Содержание

1. [Введение](#введение)
2. [Создание базы данных](#1-создание-базы-данных)
3. [Подключение к базе данных](#2-подключение-к-базе-данных)
4. [Создание регистрации](#3-создание-регистрации)
5. [Создание авторизации](#4-создание-авторизации)
6. [Страница заявок пользователя](#5-страница-заявок-пользователя)
7. [Страница создания заявок](#6-страница-создания-заявок)
8. [Функция выхода из системы](#7-выход-из-системы)
9. [Создание главной страницы админ панели](#8-создание-главной-страницы-админ-панели)
10. [Создание страницы редактирования заявок](#9-создание-страницы-редактирования-заявок-админ)

## Введение

Данный гайд поможет создать веб-приложение для клининговых услуг с использованием PHP, MySQL и Bootstrap. Приложение включает:

- Систему регистрации и авторизации пользователей
- Создание и просмотр заявок на услуги
- Админ-панель для управления заявками 
- Разделение ролей (пользователь/администратор)

**Требования:**
- Веб-сервер с поддержкой PHP (например, XAMPP, OpenServer)
- MySQL база данных
- Базовые знания HTML, CSS, PHP

## 1. Создание базы данных

### Создание базы данных

Создаем базу данных с кодировкой UTF-8 для корректной работы с русским текстом:

```sql
CREATE DATABASE demoex COLLATE utf8mb4_general_ci;
```

### Структура таблиц

Наше приложение будет использовать 4 связанные таблицы:

- `roles` - роли пользователей (администратор, пользователь)
- `users` - данные пользователей
- `statuses` - статусы заявок
- `orders` - заявки на услуги

### Таблица ролей

Создаем таблицу ролей первой, так как на неё будет ссылаться таблица пользователей:

```sql
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL
);

INSERT INTO roles (role_name) VALUES ('admin'), ('user');
```

**Объяснение полей:**
- `role_id` - уникальный идентификатор роли (автоинкремент)
- `role_name` - название роли

### Таблица пользователей

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_login VARCHAR(255) NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    user_fio VARCHAR(255) NOT NULL,
    user_phone VARCHAR(25) NOT NULL,
    user_email VARCHAR(50) NOT NULL,
    role_id INT NOT NULL DEFAULT 2,
    FOREIGN KEY (role_id) REFERENCES roles (role_id)
);
```

**Объяснение полей:**
- `user_id` - уникальный идентификатор пользователя
- `user_login` - логин для входа в систему
- `user_password` - хешированный пароль
- `user_fio` - ФИО пользователя
- `user_phone` - номер телефона
- `user_email` - электронная почта
- `role_id` - ссылка на роль (по умолчанию 2 = пользователь)

### Таблица статусов заявок

```sql
CREATE TABLE statuses (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(50) NOT NULL
);

INSERT INTO statuses (status_name) VALUES 
    ('новая'), 
    ('услуга оказана'), 
    ('услуга отклонена');
```

### Таблица заявок

```sql
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    order_service VARCHAR(255) NOT NULL,
    order_address VARCHAR(255) NOT NULL,
    order_date_of DATE NOT NULL,
    order_time TIME NOT NULL,
    order_payment_type VARCHAR(255) NOT NULL,
    order_note TEXT,
    FOREIGN KEY (status_id) REFERENCES statuses (status_id),
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);
```

**Объяснение полей:**
- `order_id` - уникальный идентификатор заявки
- `user_id` - ссылка на пользователя, создавшего заявку
- `status_id` - текущий статус заявки (по умолчанию 1 = новая)
- `order_service` - тип заказанной услуги
- `order_address` - адрес выполнения услуги
- `order_date_of` - дата выполнения
- `order_time` - время выполнения
- `order_payment_type` - способ оплаты
- `order_note` - примечание администратора (может быть пустым)

## 2. Подключение к базе данных

Создаем файл `db.php` для подключения к базе данных:

```php
<?php
$host = "localhost";          // Адрес сервера БД
$dbname = "demoex";          // Имя базы данных
$username = "root";          // Имя пользователя БД
$password = "";              // Пароль (пустой для XAMPP)

try {
    // Создаем PDO соединение с указанием кодировки
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Настраиваем режим обработки ошибок
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Альтернативная запись: $pdo->setAttribute(3, 2);
} catch (PDOException $e) {
    echo 'Ошибка подключения: ' . $e->getMessage();
    die(); // Останавливаем выполнение скрипта при ошибке
}
?>
```

**Объяснение PDO:**
- PDO (PHP Data Objects) - безопасный способ работы с базами данных
- `setAttribute` настраивает отображение ошибок для отладки
- `charset=utf8` обеспечивает корректную работу с русским текстом

## 3. Создание регистрации

Создаем файл `reg.php` для регистрации новых пользователей.

### PHP код обработки

```php
<?php
session_start();              // Запускаем сессии
require_once 'db.php';        // Подключаем файл с БД

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверяем заполнение всех обязательных полей
    if (
        empty($_POST['fio']) ||
        empty($_POST['tel']) ||
        empty($_POST['email']) ||
        empty($_POST['login']) ||
        empty($_POST['password'])
    ) {
        $message = "Заполните все поля";
    } else {
        // Сохраняем данные в переменные
        $fio = $_POST['fio'];
        $tel = $_POST['tel'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        
        // Хешируем пароль для безопасности
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Подготавливаем SQL запрос (защита от SQL-инъекций)
        $stmt = $pdo->prepare("
            INSERT INTO users (user_fio, user_phone, user_email, user_login, user_password) 
            VALUES (:user_fio, :user_phone, :user_email, :user_login, :user_password)
        ");
        
        // Выполняем запрос с параметрами
        if ($stmt->execute([
            'user_fio' => $fio,
            'user_phone' => $tel,
            'user_email' => $email,
            'user_login' => $login,
            'user_password' => $password
        ])) {
            // При успешной регистрации сохраняем данные в сессию
            $_SESSION["id"] = $pdo->lastInsertId();  // ID нового пользователя
            $_SESSION["auth"] = true;                // Метка авторизации
            $_SESSION["role_id"] = 2;                // Роль пользователя
            
            header('Location: orders.php');         // Перенаправляем на заявки
            exit();                                 // Завершаем выполнение
        } else {
            $message = "Ошибка регистрации";
        }
    }
}
?>
```

### HTML форма регистрации

```html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Регистрация</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) echo "<div class='alert alert-danger'>$message</div>" ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="fio" class="form-label">ФИО</label>
                                <input type="text" class="form-control" name="fio" placeholder="Иванов И.И." required>
                            </div>
                            <div class="mb-3">
                                <label for="tel" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" name="tel" placeholder="8(999)999-99-99" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="example@gmail.com" required>
                            </div>
                            <div class="mb-3">
                                <label for="login" class="form-label">Логин</label>
                                <input type="text" class="form-control" name="login" placeholder="example" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php">Уже есть аккаунт? Войти</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

**Важные моменты:**
- `password_hash()` создает безопасный хеш пароля
- `$pdo->lastInsertId()` возвращает ID последней вставленной записи
- `empty()` проверяет, что поле не пустое
- Используем именованные параметры для защиты от SQL-инъекций

## 4. Создание авторизации

Создаем файл `index.php` для входа в систему.

### PHP код обработки

```php
<?php
session_start();
require_once 'db.php';

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверяем заполнение полей
    if (empty($_POST['login']) || empty($_POST['password'])) {
        $message = "Заполните все поля";
    } else {
        $login = $_POST['login'];
        $password = $_POST['password'];
        
        // Ищем пользователя по логину
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_login = :user_login");
        $stmt->execute(['user_login' => $login]);
        $user = $stmt->fetch();
        
        // Проверяем существование пользователя и правильность пароля
        if ($user && password_verify($password, $user['user_password'])) {
            // Сохраняем данные в сессию
            $_SESSION["id"] = $user['user_id'];
            $_SESSION["auth"] = true;
            $_SESSION["role_id"] = $user['role_id'];
            
            // Перенаправляем в зависимости от роли
            if ($user["role_id"] == 1) {
                header('Location: admin.php');      // Админ -> админ-панель
            } else {
                header('Location: orders.php');     // Пользователь -> заявки
            }
            exit();
        } else {
            $message = "Неверный логин или пароль";
        }
    }
}
?>
```

### HTML форма авторизации

```html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Вход в систему</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) echo "<div class='alert alert-danger'>$message</div>" ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="login" class="form-label">Логин</label>
                                <input type="text" class="form-control" name="login" placeholder="example" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Войти</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="reg.php">Нет аккаунта? Зарегистрироваться</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

**Объяснение `password_verify()`:**
- Функция сравнивает введенный пароль с хешированным из БД
- Возвращает `true` если пароли совпадают, `false` если нет
- Автоматически обрабатывает хеширование, нам не нужно ничего дополнительно делать

## 5. Страница заявок пользователя

Создаем файл `orders.php` для просмотра заявок текущего пользователя.

### PHP код

```php
<?php
session_start();
include "db.php";

// Проверяем авторизацию
if (empty($_SESSION['auth'])) {
    header('Location: index.php');
    exit();
}

// Получаем заявки текущего пользователя со статусами
$stmt = $pdo->prepare("
    SELECT orders.*, statuses.status_name
    FROM orders
    LEFT JOIN statuses ON orders.status_id = statuses.status_id
    WHERE user_id = :user_id
    ORDER BY order_id DESC
");
$stmt->execute(['user_id' => $_SESSION['id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center p-3">
        <h2>Портал клининговых услуг "Мой не сам"</h2>
    </header>
    
    <nav class="bg-secondary">
        <ul class="nav justify-content-end p-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="orders.php">Мои заявки</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="create_order.php">Создать заявку</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="logout.php">Выход</a>
            </li>
        </ul>
    </nav>

    <main class="container mt-3">
        <h2 class="text-center mb-3">Мои заявки</h2>
        
        <?php if (empty($orders)): ?>
            <div class="alert alert-info text-center">
                <p>У вас пока нет заявок.</p>
                <a href="create_order.php" class="btn btn-primary">Создать первую заявку</a>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-8 col-lg-6 mb-3">
                        <div class="card">
                            <div class="card-header">
                                <h5>Заявка #<?= $order['order_id'] ?></h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li><b>Услуга:</b> <?= htmlspecialchars($order['order_service']) ?></li>
                                    <li><b>Адрес:</b> <?= htmlspecialchars($order['order_address']) ?></li>
                                    <li><b>Дата:</b> <?= date('d.m.Y', strtotime($order['order_date_of'])) ?></li>
                                    <li><b>Время:</b> <?= date('H:i', strtotime($order['order_time'])) ?></li>
                                    <li><b>Тип оплаты:</b> <?= htmlspecialchars($order['order_payment_type']) ?></li>
                                    <li><b>Статус:</b> 
                                        <span class="badge bg-<?= $order['status_id'] == 1 ? 'warning' : ($order['status_id'] == 2 ? 'success' : 'danger') ?>">
                                            <?= htmlspecialchars($order['status_name']) ?>
                                        </span>
                                    </li>
                                    
                                    <?php if (!empty($order['order_note'])): ?>
                                        <li><b>Примечание:</b> <?= htmlspecialchars($order['order_note']) ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
```

**Объяснение кода:**
- `LEFT JOIN` объединяет таблицы заявок и статусов
- `fetchAll()` получает все записи в виде массива
- `htmlspecialchars()` защищает от XSS-атак
- `date()` форматирует дату и время для вывода
- Условная окраска статусов через Bootstrap классы

### Создание тестовых заявок

Для тестирования добавьте несколько заявок в базу данных:

```sql
INSERT INTO orders (user_id, order_service, order_address, order_date_of, order_time, order_payment_type)
VALUES 
    (1, 'Мытье окон', 'ул. Ленина 10', '2025-06-01', '14:00:00', 'Банковская карта'),
    (1, 'Генеральная уборка', 'пр. Мира 5', '2025-06-03', '09:00:00', 'Наличные'),
    (1, 'Уборка после ремонта', 'ул. Советская 22', '2025-06-05', '11:30:00', 'Банковская карта');
```

## 6. Страница создания заявок

Создаем файл `create_order.php` для создания новых заявок.

### PHP код

```php
<?php
session_start();
require_once 'db.php';

// Проверяем авторизацию
if (empty($_SESSION['auth'])) {
    header('Location: index.php');
    exit();
}

// Обрабатываем отправку формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем заполнение всех полей
    if (
        empty($_POST['order_service']) ||
        empty($_POST['order_address']) ||
        empty($_POST['order_date_of']) ||
        empty($_POST['order_time']) ||
        empty($_POST['order_payment_type'])
    ) {
        $message = "Заполните все поля";
    } else {
        // Дополнительная валидация даты (не раньше завтрашнего дня)
        $order_date = $_POST['order_date_of'];
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        if ($order_date < $tomorrow) {
            $message = "Дата заявки должна быть не раньше завтрашнего дня";
        } else {
            $service = $_POST['order_service'];
            $address = $_POST['order_address'];
            $date_of = $_POST['order_date_of'];
            $time = $_POST['order_time'];
            $payment_type = $_POST['order_payment_type'];

            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, 
                    order_service, 
                    order_address, 
                    order_date_of, 
                    order_time, 
                    order_payment_type
                ) VALUES (
                    :user_id, 
                    :order_service, 
                    :order_address, 
                    :order_date_of, 
                    :order_time, 
                    :order_payment_type
                )
            ");

            if ($stmt->execute([
                'user_id' => $_SESSION['id'],
                'order_service' => $service,
                'order_address' => $address,
                'order_date_of' => $date_of,
                'order_time' => $time,
                'order_payment_type' => $payment_type
            ])) {
                header('Location: orders.php');
                exit();
            } else {
                $message = "Ошибка создания заявки";
            }
        }
    }
}
?>
```

### HTML форма

```html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создать заявку</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-primary text-white text-center p-3">
        <h2>Портал клининговых услуг "Мой не сам"</h2>
    </header>
    
    <nav class="bg-secondary">
        <ul class="nav justify-content-end p-2">
            <li class="nav-item">
                <a class="nav-link text-white" href="orders.php">Мои заявки</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="create_order.php">Создать заявку</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="logout.php">Выход</a>
            </li>
        </ul>
    </nav>

    <main class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Создание заявки</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) echo "<div class='alert alert-danger'>$message</div>" ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="order_service" class="form-label">Услуга</label>
                                <select class="form-select" name="order_service" required>
                                    <option value="" disabled selected>Выберите услугу</option>
                                    <option value="Уборка квартиры">Уборка квартиры</option>
                                    <option value="Уборка офиса">Уборка офиса</option>
                                    <option value="Генеральная уборка">Генеральная уборка</option>
                                    <option value="Химчистка">Химчистка</option>
                                    <option value="Мытье окон">Мытье окон</option>
                                    <option value="Уборка после ремонта">Уборка после ремонта</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order_address" class="form-label">Адрес</label>
                                <input type="text" class="form-control" name="order_address" 
                                       placeholder="ул. Примерная, д. 1, кв. 1" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order_date_of" class="form-label">Дата</label>
                                <input type="date" class="form-control" name="order_date_of" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order_time" class="form-label">Время</label>
                                <input type="time" class="form-control" name="order_time" 
                                       min="08:00" max="20:00" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="order_payment_type" class="form-label">Тип оплаты</label>
                                <select class="form-select" name="order_payment_type" required>
                                    <option value="" disabled selected>Выберите способ оплаты</option>
                                    <option value="Наличные">Наличные</option>
                                    <option value="Банковская карта">Банковская карта</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Создать заявку</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Устанавливаем минимальную дату (завтра)
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[type="date"]');
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateInput.min = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
```

**Дополнительные возможности:**
- Валидация даты (не раньше завтрашнего дня)
- Ограничение времени работы (8:00-20:00)
- Предустановленный список услуг
- Клиентская валидация через JavaScript

## 7. Выход из системы

Создаем файл `logout.php` для завершения сессии и выхода из аккаунта:

```php
<?php
session_start();
$_SESSION['auth'] = null;
header("Location: index.php");
```

Этот код очищает метку авторизации и перенаправляет пользователя на страницу входа. Таким образом, повторный доступ к защищённым страницам невозможен без повторной авторизации.

## 8. Создание главной страницы админ панели

Главная страница админки отображает все заявки всех пользователей. Также важно убедиться, что доступ к ней имеют только администраторы.

### Проверка прав доступа:

```php
session_start();
include "db.php";

if (empty($_SESSION['auth']) || $_SESSION['role_id'] != 1) {
    header('Location: orders.php');
    exit();
}
```

### Получение всех заявок с полной информацией:

```php
$stmt = $pdo->prepare("
    SELECT *
    FROM orders o
    LEFT JOIN statuses s ON o.status_id = s.status_id
    LEFT JOIN users u ON o.user_id = u.user_id
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

Здесь мы объединяем таблицы заявок, пользователей и статусов, чтобы получить всю нужную информацию.

### Изменения в index.php для редиректа по ролям:

```php
$_SESSION["role_id"] = $user['role_id'];

if ($user["role_id"] == 1) {
    header('Location: admin.php');
} else {
    header('Location: orders.php');
}
```

## 9. Создание страницы редактирования заявок (Админ)

Эта страница позволяет администратору изменять статус заявки и добавлять примечания.

### Проверка доступа:

```php
session_start();
include "db.php";

if (empty($_SESSION['auth']) || $_SESSION['role_id'] != 1) {
    header('Location: orders.php');
    exit();
}
```

### Обработка формы:

```php
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

    if ($stmt->execute([
        'status_id' => $_POST['status_id'],
        'order_note' => $_POST['order_note'],
        'order_id' => $_POST['order_id']
    ])) {
        header('Location: admin.php');
        exit();
    }
}
```

Здесь `order_note` может быть пустым — это нормально. Важно, что обновление происходит только при наличии `status_id` и `order_id`.

В админке должна быть ссылка на страницу редактирования с передачей ID заявки. После сохранения админ возвращается на главную страницу панели.
