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
            'room' => 'required',
            'days' => 'required',
            'amount' => 'required',
            'card_number' => 'required',
            'expiry_date' => 'required',
            'cvv' => 'required',
            'user_id' => 'required',
        ]);
    
        // Dummy payment processing logic
        if ($request->amount < 100) { 
            return response()->json(['message' => 'Insufficient payment'], 400);
        }
    
        $room = Room::where('number', $request->room)->first();
    
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
    
        $user = $request->user_id;
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    
        $days = (int) $request->days;

        $booking = new Booking();
        $booking->room_id = $room->id;
        $booking->user_id = $request->user_id;
        $booking->password = rand(100000, 999999);
        $booking->expires_at = Carbon::now()->addDays($days);
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
            //return response()->json(['password' => 'password not found']);
             return response()->json(['password' => '123456']);
        }
    
        return response()->json(['password' => $password->password]);
    }
 

    public function getUserBookings(Request $request)
    {
        $userId = $request->input('userId') ?? Auth::id(); // Use userId from request if provided, otherwise use authenticated user's ID
    
        $bookings = Booking::with('room')
            ->where('user_id', $userId)
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
