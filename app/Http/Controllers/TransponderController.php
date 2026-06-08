<?php
namespace App\Http\Controllers;

use App\Models\Transponder;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TransponderController extends Controller
{
    public function index()
    {
        $transponders = Transponder::with('vehicle.driver')->orderBy('id_transponder', 'desc')->get();
        $vehicles = Vehicle::with('driver')->orderBy('plate_number')->get();
        return view('transponders.index', compact('transponders', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_vehicle' => 'required|exists:vehicles,id_vehicle',
            'serial_number' => 'required|string|max:100|unique:transponders',
            'status' => 'required|string|in:активен,неактивен',
        ]);
        Transponder::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Transponder $transponder)
    {
        $data = $request->validate([
            'id_vehicle' => 'required|exists:vehicles,id_vehicle',
            'status' => 'required|string|in:активен,неактивен',
        ]);
        $transponder->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Transponder $transponder)
    {
        $transponder->delete();
        return response()->json(['success' => true]);
    }

    public function generateSerial()
    {
        $serial = Transponder::generateSerialNumber();
        return response()->json(['serial_number' => $serial]);
    }
}
