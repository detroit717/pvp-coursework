<?php
namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Transaction;
use App\Models\Fine;
use App\Models\Transponder;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $id = session('driver_id');
        if (!$id) {
            return redirect('/login');
        }

        $driver = Driver::with('vehicles.transponders')->findOrFail($id);

        $transactions = Transaction::with(['paymentPoint', 'lane', 'paymentMethod'])
            ->whereIn('id_vehicle', $driver->vehicles->pluck('id_vehicle'))
            ->orderBy('datetime', 'desc')
            ->limit(20)
            ->get();

        $fines = Fine::with(['fineType', 'paymentPoint'])
            ->where('id_driver', $id)
            ->orderBy('datetime', 'desc')
            ->get();

        $unpaidFines = $fines->where('payment_status', 'неоплачен');

        return view('profile.index', compact('driver', 'transactions', 'fines', 'unpaidFines'));
    }

    public function addBalance(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:10|max:100000',
        ]);

        $driver = Driver::findOrFail(session('driver_id'));
        $driver->increment('personal_balance', $data['amount']);

        return back()->with('success', 'Баланс пополнен на ' . number_format($data['amount'], 2) . ' ₽');
    }
}
