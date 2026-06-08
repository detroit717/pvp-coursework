<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $recentTransactions = Transaction::with('vehicle:id_vehicle,plate_number')
            ->orderBy('datetime', 'desc')
            ->limit(5)
            ->get();

        $stats = DB::selectOne("
            SELECT 
                COUNT(t.id_transaction) as total_transactions,
                COALESCE(SUM(t.amount), 0) as total_revenue,
                COALESCE(AVG(t.amount), 0) as avg_check,
                COUNT(DISTINCT v.id_driver) as unique_drivers
            FROM transactions t
            JOIN vehicles v ON t.id_vehicle = v.id_vehicle
            WHERE t.datetime >= NOW() - INTERVAL '30 days'
            AND t.status = 'успешно'
        ");

        return view('dashboard', compact('recentTransactions', 'stats'));
    }
}
