<?php
require_once 'db.php';
/** @var PDO $pdo */
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? null;
// Исправление: берем ID и из GET, и из POST для надежности при редактировании
$id = $_REQUEST['id'] ?? null;

if ($action === 'get_form' || $action === 'get_card') {
    try {
        if ($page === 'drivers' && $action === 'get_card' && $id) {
            $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id_driver = ?");
            $stmt->execute([$id]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($driver) {
                $stmt_v = $pdo->prepare("SELECT v.*, at.name as type_name FROM vehicles v LEFT JOIN auto_types at ON v.id_auto_type = at.id_auto_type WHERE v.id_driver = ?");
                $stmt_v->execute([$id]);
                $vehicles = $stmt_v->fetchAll(PDO::FETCH_ASSOC);
                require_once __DIR__ . '/views/driver_card.php';
            }
        } 
        // Здесь можно добавить подгрузку форм: if ($page === 'drivers' && $action === 'get_form') { ... }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    exit;
}
// В index.php, сразу после обработки get_form/get_card
// Получение данных автомобиля для редактирования (JSON)
if ($page === 'vehicles' && $action === 'get_vehicle_data' && $id) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id_vehicle = ?");
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($vehicle) {
            echo json_encode($vehicle);
        } else {
            echo json_encode(['error' => 'Автомобиль не найден']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'add_form' || $action === 'edit_form') {
    if ($page === 'drivers') {
        $driver = null;
        if ($action === 'edit_form' && $id) {
            $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id_driver = ?");
            $stmt->execute([$id]);
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        require_once __DIR__ . '/views/driver_form.php';
        exit;
    }
}

// Получение полос для формы транзакций
if ($page === 'transactions' && $action === 'get_lanes') {
    header('Content-Type: application/json');
    $point_id = $_GET['point_id'] ?? null;
    
    if ($point_id) {
        try {
            $stmt = $pdo->prepare("
                SELECT l.id_lane, l.lane_number 
                FROM lanes l 
                WHERE l.id_point = ? 
                ORDER BY l.lane_number
            ");
            $stmt->execute([$point_id]);
            $lanes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($lanes);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode([]);
    }
    exit;
}

// Обработка AJAX-запросов для полос
if (isset($_GET['action']) && $_GET['action'] === 'get_lanes' && isset($_GET['point_id'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("SELECT id_lane, lane_number FROM lanes WHERE id_point = ? ORDER BY lane_number");
        $stmt->execute([$_GET['point_id']]);
        $lanes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($lanes);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Получение данных таблицы водителей для обновления
if ($page === 'drivers' && $action === 'get_table_data') {
    header('Content-Type: application/json');
    try {
        // Получаем статистику
        $stats = $pdo->query("SELECT COUNT(*) as total, COALESCE(SUM(personal_balance), 0) as total_money FROM drivers")->fetch();
        
        // Получаем список водителей
        $drivers = $pdo->query("SELECT id_driver, full_name, phone_number, personal_balance FROM drivers ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
        
        // Форматируем данные
        $formatted_drivers = array_map(function($d) {
            return [
                'id_driver' => $d['id_driver'],
                'full_name' => htmlspecialchars($d['full_name']),
                'phone_number' => htmlspecialchars($d['phone_number']),
                'personal_balance' => number_format($d['personal_balance'], 2, ',', ' ')
            ];
        }, $drivers);
        
        echo json_encode([
            'success' => true,
            'total_drivers' => number_format($stats['total']),
            'total_balance' => number_format($stats['total_money'], 2, ',', ' '),
            'drivers' => $formatted_drivers
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
// 1. Обработка AJAX-запроса: Карточка водителя (get_card)
// Этот блок должен быть первым, чтобы вернуть только HTML карточки без шаблона сайта
if ($page === 'drivers' && $action === 'get_card' && $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id_driver = ?");
        $stmt->execute([$id]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver) {
            // Получаем связанные автомобили для отображения в карточке
            $stmt_v = $pdo->prepare("
                SELECT v.*, at.name as type_name 
                FROM vehicles v 
                LEFT JOIN auto_types at ON v.id_auto_type = at.id_auto_type 
                WHERE v.id_driver = ?
            ");
            $stmt_v->execute([$id]);
            $vehicles = $stmt_v->fetchAll(PDO::FETCH_ASSOC);

            // Подключаем файл представления карточки
            require_once __DIR__ . '/views/driver_card.php';
        } else {
            echo '<div class="alert alert-danger">Водитель не найден</div>';
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Ошибка: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    exit; // Прерываем выполнение, чтобы не выводить index.php целиком[cite: 2]
}

// 2. Обработка POST запросов (сохранение, удаление, пополнение)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'error' => ''];
    
    try {
        switch ($page) {

            case 'drivers':
            if ($action === 'add') {
                $pdo->prepare("INSERT INTO drivers (full_name, phone_number, birth_date, personal_balance) VALUES (?, ?, ?, ?)")
                    ->execute([$_POST['full_name'], $_POST['phone_number'], $_POST['birth_date'], $_POST['personal_balance'] ?? 0]);
                $response['success'] = true;
            } elseif ($action === 'edit' && $id) {
                $pdo->prepare("UPDATE drivers SET full_name = ?, phone_number = ?, birth_date = ?, personal_balance = ? WHERE id_driver = ?")
                    ->execute([$_POST['full_name'], $_POST['phone_number'], $_POST['birth_date'], $_POST['personal_balance'], $id]);
                $response['success'] = true;
            } elseif ($action === 'delete' && $id) {
                $pdo->prepare("DELETE FROM drivers WHERE id_driver = ?")->execute([$id]);
                $response['success'] = true;
            } elseif ($action === 'add_balance' && $id) {
                // Пополнение баланса
                $amount = floatval($_POST['amount']);
                if ($amount <= 0) {
                    throw new Exception("Сумма должна быть положительной");
                }
                $pdo->prepare("UPDATE drivers SET personal_balance = personal_balance + ? WHERE id_driver = ?")
                    ->execute([$amount, $id]);
                $response['success'] = true;
            }
            break;
            case 'payment_points':
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO payment_points (name, location, lanes_count) VALUES (?, ?, ?)");
                    $stmt->execute([$_POST['name'], $_POST['location'] ?? null, $_POST['lanes_count']]);
                    $response['success'] = true;
                } elseif ($action === 'edit' && $id) {
                    $stmt = $pdo->prepare("UPDATE payment_points SET name = ?, location = ?, lanes_count = ? WHERE id_point = ?");
                    $stmt->execute([$_POST['name'], $_POST['location'] ?? null, $_POST['lanes_count'], $id]);
                    $response['success'] = true;
                } elseif ($action === 'delete' && $id) {
                    $pdo->prepare("DELETE FROM payment_points WHERE id_point = ?")->execute([$id]);
                    $response['success'] = true;
                } elseif ($action === 'add_lane') {
                    $stmt = $pdo->prepare("INSERT INTO lanes (id_point, lane_number) VALUES (?, ?)");
                    $stmt->execute([$_POST['id_point'], $_POST['lane_number']]);
                    $response['success'] = true;
                } elseif ($action === 'delete_lane' && $id) {
                    $pdo->prepare("DELETE FROM lanes WHERE id_lane = ?")->execute([$id]);
                    $response['success'] = true;
                } elseif ($action === 'get_lanes' && isset($_GET['point_id'])) {
                    header('Content-Type: application/json');
                    try {
                        $stmt = $pdo->prepare("SELECT id_lane, lane_number FROM lanes WHERE id_point = ? ORDER BY lane_number");
                        $stmt->execute([$_GET['point_id']]);
                        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
                    } catch (Exception $e) {
                        echo json_encode(['error' => $e->getMessage()]);
                    }
                    exit;
                }
                break;
                
            case 'vehicles':
                if ($action === 'add') {
                    $pdo->prepare("INSERT INTO vehicles (id_auto_type, id_driver, plate_number, name) VALUES (?, ?, ?, ?)")
                        ->execute([$_POST['id_auto_type'], $_POST['id_driver'], $_POST['plate_number'], $_POST['name']]);
                    $response['success'] = true;
                } elseif ($action === 'edit' && $id) {
                    $pdo->prepare("UPDATE vehicles SET id_auto_type = ?, id_driver = ?, plate_number = ?, name = ? WHERE id_vehicle = ?")
                        ->execute([$_POST['id_auto_type'], $_POST['id_driver'], $_POST['plate_number'], $_POST['name'], $id]);
                    $response['success'] = true;
                } elseif ($action === 'delete' && $id) {
                    $pdo->prepare("DELETE FROM vehicles WHERE id_vehicle = ?")->execute([$id]);
                    $response['success'] = true;
                }
                break;
                
           case 'transactions':
                if ($action === 'add') {
                    if (empty($_POST['id_point']) || empty($_POST['id_lane']) || empty($_POST['id_vehicle'])) {
                        throw new Exception("Не все обязательные поля заполнены");
                    }

                    $id_payment_method = (int)$_POST['id_payment_method'];
                    $id_transponder = null;
                    
                    // Если способ оплаты - транспондер (id = 3), ищем активный транспондер
                    if ($id_payment_method == 3) {
                        $stmt = $pdo->prepare("SELECT id_transponder FROM transponders WHERE id_vehicle = ? AND status = 'активен' LIMIT 1");
                        $stmt->execute([$_POST['id_vehicle']]);
                        $transponder = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($transponder) {
                            $id_transponder = $transponder['id_transponder'];
                        } else {
                            throw new Exception("Для оплаты транспондером необходимо активировать устройство для данного автомобиля");
                        }
                    }
                    
                    // Проверяем, какой тариф применять
                    $id_tariff = !empty($_POST['id_tariff']) ? $_POST['id_tariff'] : null;
                    
                    // Если тариф не выбран, пробуем определить автоматически по типу авто
                    if (!$id_tariff) {
                        $stmt = $pdo->prepare("
                            SELECT t.id_tariff 
                            FROM vehicles v 
                            JOIN tariffs t ON v.id_auto_type = t.id_auto_type 
                            WHERE v.id_vehicle = ? 
                            LIMIT 1
                        ");
                        $stmt->execute([$_POST['id_vehicle']]);
                        $tariff = $stmt->fetch(PDO::FETCH_ASSOC);
                        if ($tariff) {
                            $id_tariff = $tariff['id_tariff'];
                        }
                    }
                    
                    // ВАЖНО: Формируем запрос в зависимости от наличия транспондера
                    if ($id_transponder) {
                        $stmt = $pdo->prepare("
                            INSERT INTO transactions (id_point, id_lane, id_vehicle, id_tariff, amount, id_payment_method, id_transponder, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'успешно')
                        ");
                        $stmt->execute([
                            $_POST['id_point'], 
                            $_POST['id_lane'], 
                            $_POST['id_vehicle'], 
                            $id_tariff, 
                            $_POST['amount'] ?? 0, 
                            $id_payment_method, 
                            $id_transponder
                        ]);
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO transactions (id_point, id_lane, id_vehicle, id_tariff, amount, id_payment_method, status) 
                            VALUES (?, ?, ?, ?, ?, ?, 'успешно')
                        ");
                        $stmt->execute([
                            $_POST['id_point'], 
                            $_POST['id_lane'], 
                            $_POST['id_vehicle'], 
                            $id_tariff, 
                            $_POST['amount'] ?? 0, 
                            $id_payment_method
                        ]);
                    }
                    
                    $response['success'] = true;
                }
                break;
                
            case 'fines':
                if ($action === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO fines (id_driver, id_vehicle, id_fine_type, amount, datetime, payment_status, comment) 
                        VALUES (?, ?, ?, ?, NOW(), 'неоплачен', ?)
                    ");
                    // id_vehicle может быть пустым, если штраф выписан на водителя без привязки к конкретному авто
                    $vehicle_id = !empty($_POST['id_vehicle']) ? $_POST['id_vehicle'] : null;
                    $stmt->execute([$_POST['id_driver'], $vehicle_id, $_POST['id_fine_type'], $_POST['amount'], $_POST['comment']]);
                    $response['success'] = true;
                } elseif ($action === 'pay' && $id) {
                    $stmt = $pdo->prepare("UPDATE fines SET payment_status = 'оплачен' WHERE id_fine = ?");
                    $stmt->execute([$id]);
                    $response['success'] = true;
                } elseif ($action === 'delete' && $id) {
                    $stmt = $pdo->prepare("DELETE FROM fines WHERE id_fine = ?");
                    $stmt->execute([$id]);
                    $response['success'] = true;
                }
                break;

           case 'tariffs':
            if ($action === 'add') {
                $day = ($_POST['day_of_week'] === '') ? null : $_POST['day_of_week'];
                $pdo->prepare("INSERT INTO tariffs (id_auto_type, amount, time_start, time_end, day_of_week) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$_POST['id_auto_type'], $_POST['amount'], $_POST['time_start'], $_POST['time_end'], $day]);
                $response['success'] = true;
            } elseif ($action === 'edit' && $id) {
                $day = ($_POST['day_of_week'] === '') ? null : $_POST['day_of_week'];
                $pdo->prepare("UPDATE tariffs SET id_auto_type = ?, amount = ?, time_start = ?, time_end = ?, day_of_week = ? WHERE id_tariff = ?")
                    ->execute([$_POST['id_auto_type'], $_POST['amount'], $_POST['time_start'], $_POST['time_end'], $day, $id]);
                $response['success'] = true;
            } elseif ($action === 'delete' && $id) {
                $pdo->prepare("DELETE FROM tariffs WHERE id_tariff = ?")->execute([$id]);
                $response['success'] = true;
            }
            break;
                
                if ($action === 'delete' && isset($_GET['id'])) {
                    $pdo->prepare("DELETE FROM tariffs WHERE id_tariff = ?")->execute([$_GET['id']]);
                    header("Location: ?page=tariffs");
                    exit;
                }
                include 'views/tariffs.php';
                break;
        }
    } catch (Exception $e) {
        $response['error'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// 3. Обработка данных для статистики (JSON)
if ($page === 'statistics') {
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $point_id = !empty($_GET['point_id']) ? (int)$_GET['point_id'] : null;

    // Базовое условие для фильтрации
    $where = "t.datetime::DATE BETWEEN :start AND :end AND t.status = 'успешно'";
    $params = [':start' => $start_date, ':end' => $end_date];
    
    if ($point_id) {
        $where .= " AND t.id_point = :point";
        $params[':point'] = $point_id;
    }

    if ($action === 'data') {
        header('Content-Type: application/json');
        
        try {
            // Метрики (включая уникальные автомобили)
            $stmt = $pdo->prepare("
                SELECT 
                    COALESCE(SUM(t.amount), 0) as total_revenue,
                    COALESCE(AVG(t.amount), 0) as avg_check,
                    COUNT(t.id_transaction) as total_transactions,
                    COUNT(DISTINCT v.id_driver) as unique_drivers,
                    COUNT(DISTINCT t.id_vehicle) as unique_vehicles
                FROM transactions t
                JOIN vehicles v ON t.id_vehicle = v.id_vehicle
                WHERE $where
            ");
            $stmt->execute($params);
            $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

            // График выручки по дням
            $stmt = $pdo->prepare("
                SELECT t.datetime::DATE as date, COALESCE(SUM(t.amount), 0) as revenue
                FROM transactions t
                WHERE $where
                GROUP BY t.datetime::DATE
                ORDER BY date
            ");
            $stmt->execute($params);
            $daily = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Способы оплаты
            $stmt = $pdo->prepare("
                SELECT pm.name, COALESCE(SUM(t.amount), 0) as total
                FROM transactions t
                JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
                WHERE $where
                GROUP BY pm.name
            ");
            $stmt->execute($params);
            $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Топ пунктов
            $stmt = $pdo->prepare("
                SELECT pp.name as point_name, COALESCE(SUM(t.amount), 0) as total_revenue
                FROM transactions t
                JOIN payment_points pp ON t.id_point = pp.id_point
                WHERE $where
                GROUP BY pp.name
                ORDER BY total_revenue DESC LIMIT 5
            ");
            $stmt->execute($params);
            $top_points = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Топ водителей (с их авто)
            $stmt = $pdo->prepare("
                SELECT d.full_name as driver_name, COALESCE(SUM(t.amount), 0) as total_paid
                FROM transactions t
                JOIN vehicles v ON t.id_vehicle = v.id_vehicle
                JOIN drivers d ON v.id_driver = d.id_driver
                WHERE $where
                GROUP BY d.id_driver, d.full_name
                ORDER BY total_paid DESC LIMIT 5
            ");
            $stmt->execute($params);
            $top_drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Детализация для таблицы
            $stmt = $pdo->prepare("
                SELECT 
                    pp.name as point_name, 
                    pm.name as payment_method_name,
                    COUNT(t.id_transaction) as total_transactions,
                    COALESCE(SUM(t.amount), 0) as total_amount,
                    COALESCE(AVG(t.amount), 0) as avg_amount
                FROM transactions t
                JOIN payment_points pp ON t.id_point = pp.id_point
                JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
                WHERE $where
                GROUP BY pp.name, pm.name
                ORDER BY pp.name, total_amount DESC
            ");
            $stmt->execute($params);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'metrics' => $metrics,
                'daily' => $daily,
                'payment_methods' => $payment_methods,
                'top_points' => $top_points,
                'top_drivers' => $top_drivers,
                'details' => $details
            ]);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'Ошибка БД: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'export_csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=statistics_' . date('Y-m-d') . '.csv');
        
        // Открываем поток вывода
        $output = fopen('php://output', 'w');
        
        // Добавляем BOM для корректного отображения кириллицы в Excel
        fputs($output, "\xEF\xBB\xBF");
        
        // Заголовки столбцов
        fputcsv($output, ['Пункт оплаты', 'Способ оплаты', 'Количество транзакций', 'Сумма (руб)', 'Средний чек (руб)'], ';');
        
        // Получаем данные
        $stmt = $pdo->prepare("
            SELECT 
                pp.name as point_name, 
                pm.name as payment_method_name,
                COUNT(t.id_transaction) as total_transactions,
                ROUND(SUM(t.amount), 2) as total_amount,
                ROUND(AVG(t.amount), 2) as avg_amount
            FROM transactions t
            JOIN payment_points pp ON t.id_point = pp.id_point
            JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
            WHERE $where
            GROUP BY pp.name, pm.name
            ORDER BY pp.name, total_amount DESC
        ");
        $stmt->execute($params);
        
        // Записываем строки
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row, ';');
        }
        fclose($output);
        exit;
    }
}

// 4. Предварительная загрузка справочников для вывода страниц
$auto_types = $pdo->query("SELECT id_auto_type, name FROM auto_types ORDER BY id_auto_type")->fetchAll();
$payment_methods = $pdo->query("SELECT id_payment_method, name FROM payment_methods")->fetchAll();
$payment_points = $pdo->query("SELECT id_point, name FROM payment_points ORDER BY name")->fetchAll();
$drivers_list = $pdo->query("SELECT id_driver, full_name FROM drivers ORDER BY full_name")->fetchAll();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Платная дорога - Управление</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <link href="style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Навигация -->
    <nav class="navbar navbar-dark bg-dark fixed-top shadow">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand fw-bold" href="?page=dashboard">СИСТЕМА ПВП</a>
            <div class="text-white small d-none d-md-block">Администратор: v1.0.4</div>
        </div>
    </nav>

    <!-- Боковое меню -->
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title">Навигация</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="nav nav-pills flex-column mt-3">
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'dashboard' ? 'active' : '' ?>" href="?page=dashboard"><i class="bi bi-speedometer2 me-2"></i>Главная</a></li>
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'payment_points' ? 'active' : '' ?>" href="?page=payment_points"><i class="bi bi-geo-alt me-2"></i>Пункты оплаты</a></li>
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'drivers' ? 'active' : '' ?>" href="?page=drivers"><i class="bi bi-people me-2"></i>Водители</a></li>
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'vehicles' ? 'active' : '' ?>" href="?page=vehicles"><i class="bi bi-truck me-2"></i>Автомобили</a></li>
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'transactions' ? 'active' : '' ?>" href="?page=transactions"><i class="bi bi-currency-exchange me-2"></i>Транзакции</a></li>
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'fines' ? 'active' : '' ?>" href="?page=fines"><i class="bi bi-exclamation-octagon me-2"></i>Штрафы</a></li>
                <li class="nav-item"><a class="nav-link text-white <?= $page == 'statistics' ? 'active' : '' ?>" href="?page=statistics"><i class="bi bi-bar-chart me-2"></i>Статистика</a></li>
            </ul>
        </div>
    </div>

    <!-- Основной контент -->
    <main class="container-fluid py-4 mt-5">
        <?php
        $viewFile = __DIR__ . '/views/' . $page . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            require_once __DIR__ . '/views/dashboard.php';
        }
        ?>
    </main>

    <footer class="footer mt-auto py-3 bg-white border-top">
        <div class="container text-center">
            <span class="text-muted small">© <?= date('Y') ?> ПВП Управление. База: PostgreSQL.</span>
        </div>
    </footer>

    <script src="script.js">
        function quickRefill(driverId) {
            const amount = document.getElementById('refillAmount').value;
            if (!amount || amount <= 0) {
                alert('Пожалуйста, введите корректную сумму.');
                return;
            }

            const formData = new FormData();
            formData.append('amount', amount);

            fetch(`?page=drivers&action=add_balance&id=${driverId}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Баланс успешно пополнен!');
                    location.reload(); // Перезагружаем страницу для обновления данных
                } else {
                    alert('Ошибка пополнения: ' + (data.error || 'Неизвестная ошибка'));
                }
            })
            .catch(err => console.error('Ошибка:', err));
        }
    </script>
</body>
</html>