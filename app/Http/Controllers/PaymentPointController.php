<?php
namespace App\Http\Controllers;

use App\Models\PaymentPoint;
use App\Models\Tariff;
use App\Models\AutoType;
use App\Models\LaneType;
use Illuminate\Http\Request;

class PaymentPointController extends Controller
{
    public function index()
    {
        $points = PaymentPoint::withCount('lanes as active_lanes')
            ->withCount('transactions as total_transactions')
            ->get();

        foreach ($points as $p) {
            $p->total_revenue = $p->transactions()->where('status', 'успешно')->sum('amount');
        }

        $tariffs = Tariff::with('autoType')->get()->sortBy(fn($t) => $t->autoType?->name);
        $autoTypes = AutoType::orderBy('name')->get();
        $laneTypes = LaneType::orderBy('name')->get();
        return view('payment_points.index', compact('points', 'tariffs', 'autoTypes', 'laneTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'lanes_count' => 'required|integer|min:1',
        ]);
        PaymentPoint::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, PaymentPoint $point)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'lanes_count' => 'required|integer|min:1',
        ]);
        $point->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(PaymentPoint $point)
    {
        $point->delete();
        return response()->json(['success' => true]);
    }
}
