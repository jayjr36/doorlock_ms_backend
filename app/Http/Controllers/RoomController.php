<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Password;
use Carbon\Carbon;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::whereDoesntHave('booking', function ($query) {
            $query->where('expires_at', '>', now());
        })->get();

        return response()->json($rooms);
    }
    public function selectRoom(Request $request)
    {
        $room = Room::find($request->room);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $password = new Password();
        $password->room_id = $room->id;
        $password->password = rand(100000, 999999);
        $password->expires_at = Carbon::now()->addMinutes(5);
        $password->save();

        return response()->json(['password' => $password->password, 'expiry_time' => $password->expires_at]);
    }
}
