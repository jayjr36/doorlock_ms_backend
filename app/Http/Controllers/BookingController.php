<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function bookRoom(Request $request)
    {
        $request->validate([
            'room' => 'required|exists:rooms,number',
            'days' => 'required|integer|min:1',
            'amount' => 'required|numeric',
            'card_number' => 'required|string',
            'expiry_date' => 'required|string',
            'cvv' => 'required|string',
        ]);

        // Dummy payment processing logic
        if ($request->amount < 100) { 
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

    public function getRoomPassword($roomNumber)
    {
        $room = Room::where('number', $roomNumber)->first();
    
        if (!$room) {
            return response()->json(['password' => 'room not found']);
           // return response()->json(['password' => '123456']);
        }
    
        $password = Booking::where('room_id', $roomNumber)
                            ->where('expires_at', '>', now())
                            ->latest()
                            ->first();
    
        if (!$password) {
            return response()->json(['password' => 'password not found']);
            // return response()->json(['password' => '123456']);
        }
    
        return response()->json(['password' => $password->password]);
    }
 

    public function getUserBookings(Request $request)
    {
        $user = Auth::user();
    
        $bookings = Booking::with('room')
            ->where('user_id', $user->id)
            ->get();
    
        $formattedBookings = $bookings->map(function($booking) {
            return [
                'room_number' => $booking->room->number,
                'password' => $booking->password,
                'expiry_time' => $booking->expires_at,
            ];
        });
    
        return response()->json($formattedBookings);
    }
    
}
