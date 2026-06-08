<?php
namespace App\Http\Controllers;

use App\Models\Lane;
use Illuminate\Http\Request;

class LaneController extends Controller
{
    public function index($pointId)
    {
        $lanes = Lane::where('id_point', $pointId)->orderBy('lane_number')->get();
        return response()->json($lanes);
    }

    public function show(Lane $lane)
    {
        return response()->json($lane);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_point' => 'required|exists:payment_points,id_point',
            'lane_number' => 'required|integer|min:1',
            'id_lane_type' => 'required|exists:lane_types,id_lane_type',
        ]);
        Lane::create($data);
        return response()->json(['success' => true]);
    }

    public function update(Request $request, Lane $lane)
    {
        $data = $request->validate([
            'lane_number' => 'required|integer|min:1',
            'id_lane_type' => 'required|exists:lane_types,id_lane_type',
        ]);
        $lane->update($data);
        return response()->json(['success' => true]);
    }

    public function destroy(Lane $lane)
    {
        $lane->delete();
        return response()->json(['success' => true]);
    }
}
