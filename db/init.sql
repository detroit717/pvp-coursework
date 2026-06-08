-- ============================================
-- ИС УПВП. Создание структуры базы данных
-- СУБД: PostgreSQL 16
-- ============================================

-- 1. Типы автомобилей
CREATE TABLE auto_types (
    id_auto_type SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- 2. Водители
CREATE TABLE drivers (
    id_driver SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    birth_date DATE,
    personal_balance NUMERIC(10,2) DEFAULT 0.00 CHECK (personal_balance >= 0)
);

-- 3. Автомобили
CREATE TABLE vehicles (
    id_vehicle SERIAL PRIMARY KEY,
    id_auto_type INTEGER NOT NULL REFERENCES auto_types(id_auto_type) ON UPDATE CASCADE,
    id_driver INTEGER NOT NULL REFERENCES drivers(id_driver) ON DELETE CASCADE,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255)
);

-- 4. Пункты взимания платы
CREATE TABLE payment_points (
    id_point SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    lanes_count INTEGER DEFAULT 1 CHECK (lanes_count > 0)
);

-- 5. Полосы движения
CREATE TABLE lanes (
    id_lane SERIAL PRIMARY KEY,
    id_point INTEGER NOT NULL REFERENCES payment_points(id_point) ON DELETE CASCADE,
    lane_number INTEGER NOT NULL,
    lane_type VARCHAR(50) DEFAULT 'Универсальный',
    lane_price NUMERIC(10,2),
    lane_status VARCHAR(20) DEFAULT 'активна'
);

-- 6. Способы оплаты
CREATE TABLE payment_methods (
    id_payment_method SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- 7. Тарифы
CREATE TABLE tariffs (
    id_tariff SERIAL PRIMARY KEY,
    id_auto_type INTEGER NOT NULL REFERENCES auto_types(id_auto_type) ON UPDATE CASCADE,
    amount NUMERIC(10,2) NOT NULL CHECK (amount > 0),
    time_start TIME DEFAULT '00:00',
    time_end TIME DEFAULT '23:59',
    day_of_week INTEGER CHECK (day_of_week BETWEEN 0 AND 6)
);

-- 8. Транспондеры
CREATE TABLE transponders (
    id_transponder SERIAL PRIMARY KEY,
    id_vehicle INTEGER NOT NULL REFERENCES vehicles(id_vehicle) ON DELETE CASCADE,
    serial_number VARCHAR(100) NOT NULL UNIQUE,
    status VARCHAR(20) DEFAULT 'активен' CHECK (status IN ('активен', 'неактивен'))
);

-- 9. Типы штрафов
CREATE TABLE fine_types (
    id_fine_type SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- 10. Транзакции
CREATE TABLE transactions (
    id_transaction SERIAL PRIMARY KEY,
    id_point INTEGER NOT NULL REFERENCES payment_points(id_point),
    id_lane INTEGER NOT NULL REFERENCES lanes(id_lane),
    id_vehicle INTEGER NOT NULL REFERENCES vehicles(id_vehicle),
    id_tariff INTEGER REFERENCES tariffs(id_tariff),
    amount NUMERIC(10,2) NOT NULL CHECK (amount >= 0),
    id_payment_method INTEGER NOT NULL REFERENCES payment_methods(id_payment_method),
    id_transponder INTEGER REFERENCES transponders(id_transponder),
    status VARCHAR(20) DEFAULT 'успешно' CHECK (status IN ('успешно', 'неоплата')),
    datetime TIMESTAMP DEFAULT NOW()
);

-- 11. Штрафы
CREATE TABLE fines (
    id_fine SERIAL PRIMARY KEY,
    id_driver INTEGER NOT NULL REFERENCES drivers(id_driver) ON DELETE CASCADE,
    id_vehicle INTEGER REFERENCES vehicles(id_vehicle) ON DELETE SET NULL,
    id_transaction INTEGER REFERENCES transactions(id_transaction) ON DELETE SET NULL,
    id_point INTEGER REFERENCES payment_points(id_point),
    id_fine_type INTEGER NOT NULL REFERENCES fine_types(id_fine_type),
    amount NUMERIC(10,2) NOT NULL CHECK (amount > 0),
    datetime TIMESTAMP DEFAULT NOW(),
    payment_status VARCHAR(20) DEFAULT 'неоплачен' CHECK (payment_status IN ('оплачен', 'неоплачен')),
    comment TEXT
);

-- Индексы
CREATE INDEX idx_trans_date ON transactions(datetime);
CREATE INDEX idx_veh_plate ON vehicles(plate_number);
CREATE INDEX idx_fines_driver ON fines(id_driver);
CREATE INDEX idx_fines_status ON fines(payment_status);
CREATE INDEX idx_trans_point ON transactions(id_point);
CREATE INDEX idx_trans_vehicle ON transactions(id_vehicle);

-- ============================================
-- Хранимые процедуры и триггеры
-- ============================================

-- 1. Функция получения долга водителя
CREATE OR REPLACE FUNCTION get_driver_debt(p_driver_id INTEGER)
RETURNS NUMERIC(10,2) LANGUAGE plpgsql AS $$
DECLARE
    total_debt NUMERIC(10,2);
BEGIN
    SELECT COALESCE(SUM(amount), 0) INTO total_debt
    FROM fines
    WHERE id_driver = p_driver_id AND payment_status = 'неоплачен';
    RETURN total_debt;
END;
$$;

-- 2. Триггер: автоначисление штрафа при неоплате
CREATE OR REPLACE FUNCTION auto_fine_for_non_payment()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.status = 'неоплата' THEN
        INSERT INTO fines
            (id_driver, id_vehicle, id_transaction,
             id_point, id_fine_type, amount,
             datetime, payment_status, comment)
        SELECT v.id_driver, NEW.id_vehicle,
               NEW.id_transaction, NEW.id_point,
               1, 500.00, NOW(), 'неоплачен',
               'Автоматический штраф за неоплату'
        FROM vehicles v WHERE v.id_vehicle = NEW.id_vehicle;
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_auto_fine
AFTER INSERT ON transactions
FOR EACH ROW EXECUTE FUNCTION auto_fine_for_non_payment();

-- ============================================
-- Начальные данные
-- ============================================

-- Типы автомобилей
INSERT INTO auto_types (name) VALUES
    ('Легковой'),
    ('Грузовой'),
    ('Автобус'),
    ('Мотоцикл');

-- Способы оплаты
INSERT INTO payment_methods (name) VALUES
    ('Наличные'),
    ('Банковская карта'),
    ('Транспондер');

-- Типы штрафов
INSERT INTO fine_types (name) VALUES
    ('Неоплата проезда'),
    ('Превышение скорости'),
    ('Нарушение весовых норм'),
    ('Прочие нарушения');
