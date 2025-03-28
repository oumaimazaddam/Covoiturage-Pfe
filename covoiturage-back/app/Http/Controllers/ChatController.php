<?php

namespace App\Http\Controllers;

use App\Models\ChMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Events\MessageSent;

class ChatController extends Controller
{
    /**
     * Récupérer les messages entre l'utilisateur connecté et un autre utilisateur.
     */
    public function index($userId)
    {
        $authUserId = Auth::id();

        // Récupérer les messages entre l'utilisateur authentifié et un autre utilisateur
        $messages = ChMessage::where(function ($query) use ($authUserId, $userId) {
                $query->where('from_id', $authUserId)->where('to_id', $userId);
            })
            ->orWhere(function ($query) use ($authUserId, $userId) {
                $query->where('from_id', $userId)->where('to_id', $authUserId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Envoyer un message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'to_id' => 'required|exists:users,id',
            'body' => 'nullable|string|max:5000',
            'attachment' => 'nullable|string',
        ]);

        $message = ChMessage::create([
            'id' => Str::uuid(),
            'from_id' => Auth::id(),
            'to_id' => $request->to_id,
            'body' => $request->body,
            'attachment' => $request->attachment,
            'seen' => false,
        ]);
        broadcast(new MessageSent($message));


        return response()->json(['message' => 'Message envoyé avec succès', 'data' => $message]);
    }

    /**
     * Marquer un message comme lu.
     */
    public function markAsSeen($messageId)
    {
        $message = ChMessage::where('id', $messageId)->where('to_id', Auth::id())->firstOrFail();
        $message->update(['seen' => true]);

        return response()->json(['message' => 'Message marqué comme lu']);
    }

    /**
     * Supprimer un message.
     */
    public function destroy($messageId)
    {
        $message = ChMessage::where('id', $messageId)->where('from_id', Auth::id())->firstOrFail();
        $message->delete();

        return response()->json(['message' => 'Message supprimé']);
    }
}
