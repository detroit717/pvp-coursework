<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Lane;
use App\Models\Vehicle;
use App\Models\PaymentPoint;
use App\Models\PaymentMethod;
use App\Models\Tariff;
use App\Models\Transponder;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with(['paymentPoint', 'lane', 'vehicle.driver', 'paymentMethod'])
            ->orderBy('datetime', 'desc')
            ->limit(500)
            ->get();

        $points = PaymentPoint::orderBy('name')->get();
        $methods = PaymentMethod::all();
        $vehicles = Vehicle::with('driver')->orderBy('plate_number')->get();
        $tariffs = Tariff::with('autoType')->get()->sortBy(fn($t) => $t->autoType?->name);

        return view('transactions.index', compact('transactions', 'points', 'methods', 'vehicles', 'tariffs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_point' => 'required|exists:payment_points,id_point',
            'id_lane' => 'required|exists:lanes,id_lane',
            'id_vehicle' => 'required|exists:vehicles,id_vehicle',
            'amount' => 'required|numeric|min:0',
            'id_payment_method' => 'required|exists:payment_methods,id_payment_method',
            'id_tariff' => 'nullable|exists:tariffs,id_tariff',
        ]);

        $data['status'] = 'успешно';
        $data['datetime'] = now();

        if ((int)$data['id_payment_method'] === 3) {
            $transponder = Transponder::where('id_vehicle', $data['id_vehicle'])
                ->where('status', 'активен')->first();
            if (!$transponder) {
                return response()->json(['success' => false, 'error' => 'Нет активного транспондера']);
            }
            $data['id_transponder'] = $transponder->id_transponder;
        }

        if (empty($data['id_tariff'])) {
            $vehicle = Vehicle::with('autoType.tariffs')->find($data['id_vehicle']);
            $tariff = $vehicle->autoType->tariffs->first();
            if ($tariff) {
                $data['id_tariff'] = $tariff->id_tariff;
            } else {
                $data['id_tariff'] = null;
            }
        }

        Transaction::create($data);
        return response()->json(['success' => true]);
    }

    public function getLanesByPoint($pointId)
    {
        $lanes = Lane::with('laneType')
            ->where('id_point', $pointId)
            ->orderBy('lane_number')
            ->get();
        return response()->json($lanes);
    }
}
