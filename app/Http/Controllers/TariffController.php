<?php
namespace App\Http\Controllers;

use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        return response()->json(Tariff::with('autoType')->get()->sortBy(fn($t) => $t->autoType?->name)->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_auto_type' => 'required|exists:auto_types,id_auto_type',
            'amount' => 'required|numeric|min:0.01',
            'time_start' => 'required',
            'time_end' => 'required',
            'day_of_week' => 'nullable|integer|between:1,6',
        ]);
        Tariff::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Tariff $tariff)
    {
        $data = $request->validate([
            'id_auto_type' => 'required|exists:auto_types,id_auto_type',
            'amount' => 'required|numeric|min:0.01',
            'time_start' => 'required',
            'time_end' => 'required',
            'day_of_week' => 'nullable|integer|between:1,6',
        ]);
        $tariff->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Tariff $tariff)
    {
        $tariff->delete();
        return response()->json(['success' => true]);
    }
}
