<?php
namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\AutoType;
use App\Models\Driver;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('autoType', 'driver')->orderBy('id_vehicle')->get();
        $autoTypes = AutoType::orderBy('name')->get();
        $drivers = Driver::orderBy('full_name')->get();
        return view('vehicles.index', compact('vehicles', 'autoTypes', 'drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles',
            'name' => 'nullable|string|max:255',
            'id_auto_type' => 'required|exists:auto_types,id_auto_type',
            'id_driver' => 'required|exists:drivers,id_driver',
        ]);
        Vehicle::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'plate_number' => 'required|string|max:20|unique:vehicles,plate_number,'.$vehicle->id_vehicle.',id_vehicle',
            'name' => 'nullable|string|max:255',
            'id_auto_type' => 'required|exists:auto_types,id_auto_type',
            'id_driver' => 'required|exists:drivers,id_driver',
        ]);
        $vehicle->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(['success' => true]);
    }

    public function getData(Vehicle $vehicle)
    {
        return response()->json($vehicle);
    }
}
