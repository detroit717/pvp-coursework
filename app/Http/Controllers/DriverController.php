<?php
namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::orderBy('full_name')->get();
        $stats = Driver::selectRaw('COUNT(*) as total, COALESCE(SUM(personal_balance), 0) as total_money')->first();
        return view('drivers.index', compact('drivers', 'stats'));
    }

    public function getTableData()
    {
        $drivers = Driver::orderBy('full_name')->get();
        return response()->json([
            'success' => true,
            'total_drivers' => number_format($drivers->count()),
            'total_balance' => number_format($drivers->sum('personal_balance'), 2, ',', ' '),
            'drivers' => $drivers->map(fn($d) => [
                'id_driver' => $d->id_driver,
                'full_name' => $d->full_name,
                'phone_number' => $d->phone_number,
                'personal_balance' => number_format($d->personal_balance, 2, ',', ' '),
            ]),
        ]);
    }

    public function create()
    {
        return view('drivers.form', ['driver' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'personal_balance' => 'nullable|numeric|min:0',
        ]);
        $data['personal_balance'] ??= 0;
        Driver::create($data);
        return response()->json(['success' => true]);
    }

    public function edit(Driver $driver)
    {
        return view('drivers.form', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'personal_balance' => 'nullable|numeric|min:0',
        ]);
        $driver->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return response()->json(['success' => true]);
    }

    public function card(Driver $driver)
    {
        $driver->load('vehicles.autoType', 'vehicles.transponders');
        $debt = $driver->debt;
        $stats = $driver->vehicles()
            ->join('transactions', 'vehicles.id_vehicle', '=', 'transactions.id_vehicle')
            ->where('transactions.status', 'успешно')
            ->selectRaw('COUNT(transactions.id_transaction) as total_trips, COALESCE(SUM(transactions.amount), 0) as total_spent, MAX(transactions.datetime) as last_trip')
            ->first();
        return view('drivers.card', compact('driver', 'debt', 'stats'));
    }

    public function addBalance(Request $request, Driver $driver)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);
        $driver->increment('personal_balance', $request->amount);
        return response()->json(['success' => true, 'new_balance' => $driver->fresh()->personal_balance]);
    }
}
