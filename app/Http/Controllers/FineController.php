<?php
namespace App\Http\Controllers;

use App\Models\Fine;
use App\Models\FineType;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function index()
    {
        $fines = Fine::with(['driver', 'vehicle', 'fineType'])
            ->orderBy('datetime', 'desc')
            ->get();
        $fineTypes = FineType::all();
        $drivers = Driver::orderBy('full_name')->get();
        $vehicles = Vehicle::with('driver')->orderBy('plate_number')->get();
        return view('fines.index', compact('fines', 'fineTypes', 'drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_driver' => 'required|exists:drivers,id_driver',
            'id_vehicle' => 'nullable|exists:vehicles,id_vehicle',
            'id_fine_type' => 'required|exists:fine_types,id_fine_type',
            'amount' => 'required|numeric|min:0.01',
            'comment' => 'nullable|string',
        ]);
        $data['datetime'] = now();
        Fine::create($data);
        return response()->json(['success' => true]);
    }

    public function pay(Fine $fine)
    {
        $fine->update(['payment_status' => 'оплачен']);
        return response()->json(['success' => true]);
    }

    public function destroy(Fine $fine)
    {
        $fine->delete();
        return response()->json(['success' => true]);
    }
}
