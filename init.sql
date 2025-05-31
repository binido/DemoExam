CREATE TABLE
    roles (
        role_id INT PRIMARY KEY AUTO_INCREMENT,
        role_name VARCHAR(50) NOT NULL
    );

INSERT INTO
    roles (role_name)
VALUES
    ('admin'),
    ('user');

CREATE TABLE
    users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        user_login VARCHAR(255) NOT NULL,
        user_password VARCHAR(255) NOT NULL,
        user_fio VARCHAR(255) NOT NULL,
        user_phone VARCHAR(25) NOT NULL,
        user_email VARCHAR(50) NOT NULL,
        role_id INT NOT NULL DEFAULT (2),
        FOREIGN KEY (role_id) REFERENCES roles (role_id)
    );

CREATE TABLE
    statuses (
        status_id INT PRIMARY KEY AUTO_INCREMENT,
        status_name VARCHAR(50) NOT NULL
    );

INSERT INTO
    statuses (status_name)
VALUES
    ('новая'),
    ('услуга оказана'),
    ('услуга отклонена');

CREATE TABLE
    orders (
        order_id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        status_id INT NOT NULL DEFAULT (1),
        order_service VARCHAR(255) NOT NULL,
        order_address VARCHAR(255) NOT NULL,
        order_date_of DATE NOT NULL,
        order_time TIME NOT NULL,
        order_payment_type VARCHAR(255) NOT NULL,
        -- order_payment_type ENUM('наличные', 'карта') NOT NULL, -- Для ограничения типов оплаты
        order_note TEXT,
        FOREIGN KEY (status_id) REFERENCES statuses (status_id),
        FOREIGN KEY (user_id) REFERENCES users (user_id)
    );

-- Код для создания тестовой записи в таблице orders
-- INSERT INTO orders (user_id, order_service, order_address, order_date_of, order_time, order_payment_type)
-- VALUES 
--     (1, 'Мытье окон', 'ул. Ленина 10', '2025-05-26', '14:00:00', 'карта'),
--     (1, 'Генеральная уборка', 'пр. Мира 5', '2025-05-28', '09:00:00', 'онлайн'),
--     (1, 'Уборка после ремонта', 'ул. Советская 22', '2025-05-30', '11:30:00', 'наличные');
