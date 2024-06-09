<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function bookRoom(Request $request)
    {
        $request->validate([
            'room' => 'required|exists:rooms,number',
            'days' => 'required|integer|min=1',
            'amount' => 'required|numeric',
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvv' => 'required|string',
        ]);

        // Dummy payment processing logic
        if ($request->amount < 100) { // Assume minimum amount for demo
            return response()->json(['message' => 'Insufficient payment'], 400);
        }

        $room = Room::where('number', $request->room)->first();

        $booking = new Booking();
        $booking->room_id = $room->id;
        $booking->user_id = $request->user()->id;
        $booking->password = rand(100000, 999999);
        $booking->expires_at = Carbon::now()->addDays($request->days);
        $booking->save();

        return response()->json(['password' => $booking->password, 'expiry_time' => $booking->expires_at]);
    }
}
