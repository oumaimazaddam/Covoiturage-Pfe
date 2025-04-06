<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageDeleted;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ChatController extends Controller
{
    public function index($userId)
    {
        $authUserId = Auth::id();

        // Récupérer les messages entre l'utilisateur authentifié et un autre utilisateur
        $messages = Message::where(function ($query) use ($authUserId, $userId) {
                $query->where('from_id', $authUserId)->where('to_id', $userId);
            })
            ->orWhere(function ($query) use ($authUserId, $userId) {
                $query->where('from_id', $userId)->where('to_id', $authUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
    public function sendMessage(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'trip_id' => 'required|exists:trips,id',
            'attachment' => 'nullable|string',
        ]);

        $user = auth::user(); // JWT auth
        $message = Message::create([
            'user_id' => $user->id,
            'trip_id' => $request->trip_id,
            'content' => $request->content,
            'attachment' => $request->attachment,
            'seen' => false,
        ]);

        broadcast(new MessageSent($message->content, $user->id, $request->trip_id))->toOthers();
        
    

        return response()->json(['message' => $message->load('user')],201); // Include user data
    }

    public function fetchMessages($tripId)
    {
        $messages = Message::where('trip_id', $tripId)->with('user')->get();
        return response()->json($messages);
    }
    public function deleteMessage(Request $request, $messageId)
{
    $user = Auth::user();

    $message = Message::where('id', $messageId)
                     ->where('trip_id', $request->trip_id)
                     ->firstOrFail();

    if ($message->user_id !== $user->id) {
        return response()->json(['error' => 'You can only delete your own messages'], 403);
    }

    $tripId = $message->trip_id; // Store trip_id before deletion
    $message->delete();

    broadcast(new MessageDeleted($messageId, $tripId))->toOthers();

    return response()->json(['message' => 'Message deleted successfully'], 200);
}
}