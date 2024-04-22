<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function createNotification(Request $request)
    {
        // Validate the request data
        $request->validate([
            'message' => 'required',
            'user_id' => 'required|exists:users,id',
        ]);

        // Create the notification
        $notification = Notification::create([
            'message' => $request->message,
            'user_id' => $request->user_id,
            'desc'=>$request->desc
        ]);

        return response()->json(['message' => 'Notification created successfully', 'notification' => $notification], 201);
    }
    public function getNotifications(Request $request)
    {
        // Retrieve notifications for a specific user
        $notifications = Notification::with('user')->paginate(30);

        return response()->json(['notifications' => $notifications]);
    }

    public function searchNotification ( Request $request , $search , $sortBy , $sortDir ) {
        $page = 15;
        if ($search == "all") {
            $notifications = Notification::with('user')->
            orderBy($sortBy, $sortDir)
            ->paginate($page);
        }else{
            $notifications = Notification::with('user')->
            where( 'messsage', 'LIKE', "%$search%")
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);

        }
        return response()->json($notifications, 200);
    }

    public function deleteNotification(Request $request, $id)
    {
        // Find the notification
        $notification = Notification::find($id);

        // Check if the notification exists
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        // Check if the user is authorized to delete the notification
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the notification
        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully']);
    }

}
