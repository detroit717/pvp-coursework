<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index()
    {
        $points = DB::table('payment_points')->orderBy('name')->get(['id_point', 'name']);
        return view('statistics.index', compact('points'));
    }

    public function getData(Request $request)
    {
        $start = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $end = $request->get('end_date', now()->format('Y-m-d'));
        $pointId = $request->filled('point_id') ? (int)$request->point_id : null;

        $where = "AND t.status = 'успешно' AND t.datetime::DATE BETWEEN :start AND :end";
        $params = [':start' => $start, ':end' => $end];

        if ($pointId) {
            $where .= " AND t.id_point = :point";
            $params[':point'] = $pointId;
        }

        $metrics = DB::selectOne("
            SELECT
                COALESCE(SUM(t.amount), 0) as total_revenue,
                COALESCE(AVG(t.amount), 0) as avg_check,
                COUNT(t.id_transaction) as total_transactions,
                COUNT(DISTINCT v.id_driver) as unique_drivers,
                COUNT(DISTINCT t.id_vehicle) as unique_vehicles
            FROM transactions t
            JOIN vehicles v ON t.id_vehicle = v.id_vehicle
            WHERE 1=1 $where
        ", $params);

        $daily = DB::select("
            SELECT t.datetime::DATE as date, COALESCE(SUM(t.amount), 0) as revenue
            FROM transactions t
            WHERE 1=1 $where
            GROUP BY t.datetime::DATE ORDER BY date
        ", $params);

        $paymentMethods = DB::select("
            SELECT pm.name, COALESCE(SUM(t.amount), 0) as total
            FROM transactions t
            JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
            WHERE 1=1 $where
            GROUP BY pm.name
        ", $params);

        $topPoints = DB::select("
            SELECT pp.name as point_name, COALESCE(SUM(t.amount), 0) as total_revenue
            FROM transactions t
            JOIN payment_points pp ON t.id_point = pp.id_point
            WHERE 1=1 $where
            GROUP BY pp.name ORDER BY total_revenue DESC LIMIT 5
        ", $params);

        $topDrivers = DB::select("
            SELECT d.full_name as driver_name, COALESCE(SUM(t.amount), 0) as total_paid
            FROM transactions t
            JOIN vehicles v ON t.id_vehicle = v.id_vehicle
            JOIN drivers d ON v.id_driver = d.id_driver
            WHERE 1=1 $where
            GROUP BY d.id_driver, d.full_name ORDER BY total_paid DESC LIMIT 5
        ", $params);

        $details = DB::select("
            SELECT pp.name as point_name, pm.name as payment_method_name,
                COUNT(t.id_transaction) as total_transactions,
                COALESCE(SUM(t.amount), 0) as total_amount,
                COALESCE(AVG(t.amount), 0) as avg_amount
            FROM transactions t
            JOIN payment_points pp ON t.id_point = pp.id_point
            JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
            WHERE 1=1 $where
            GROUP BY pp.name, pm.name ORDER BY pp.name, total_amount DESC
        ", $params);

        return response()->json([
            'metrics' => $metrics,
            'daily' => $daily,
            'payment_methods' => $paymentMethods,
            'top_points' => $topPoints,
            'top_drivers' => $topDrivers,
            'details' => $details,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $start = $request->get('start_date', now()->subMonth()->format('Y-m-d'));
        $end = $request->get('end_date', now()->format('Y-m-d'));
        $pointId = $request->filled('point_id') ? (int)$request->point_id : null;

        $where = "AND t.status = 'успешно' AND t.datetime::DATE BETWEEN '$start' AND '$end'";
        if ($pointId) {
            $where .= " AND t.id_point = $pointId";
        }

        $rows = DB::select("
            SELECT pp.name as point_name, pm.name as payment_method_name,
                COUNT(t.id_transaction) as total_transactions,
                ROUND(SUM(t.amount)::numeric, 2) as total_amount,
                ROUND(AVG(t.amount)::numeric, 2) as avg_amount
            FROM transactions t
            JOIN payment_points pp ON t.id_point = pp.id_point
            JOIN payment_methods pm ON t.id_payment_method = pm.id_payment_method
            WHERE 1=1 $where
            GROUP BY pp.name, pm.name ORDER BY pp.name, total_amount DESC
        ");

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Пункт оплаты', 'Способ оплаты', 'Количество', 'Сумма (руб)', 'Средний чек (руб)'], ';');
        foreach ($rows as $row) {
            fputcsv($output, (array)$row, ';');
        }
        fclose($output);

        return response()->stream(function() {}, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=statistics_' . date('Y-m-d') . '.csv',
        ]);
    }
}
