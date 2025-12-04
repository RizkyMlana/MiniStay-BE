<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    
    public function sendMessageAsUser(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $chat = Chat::create([
            'user_id' => auth('user')->id(),
            'admin_id' => null,
            'message' => $request->message,
            'sender' => 'user',
            'is_seen' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $chat
        ]);
    }



    
    public function myChats()
    {
        $chats = Chat::where('user_id', auth('user')->id())
            ->orderBy('id', 'asc')
            ->get();

        // auto mark admin messages as seen
        Chat::where('user_id', auth('user')->id())
            ->where('sender', 'admin')
            ->where('is_seen', false)
            ->update(['is_seen' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $chats
        ]);
    }



    public function listActiveChats()
    {
        // list semua user yang pernah chat
        $users = Chat::select('user_id')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->with('user:id,name,phone')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    public function getMessagesWithUser($user_id)
    {
        $messages = Chat::where('user_id', $user_id)
            ->orderBy('id', 'asc')
            ->get();

        // admin melihat chat user → mark as seen
        Chat::where('user_id', $user_id)
            ->where('sender', 'user')
            ->where('is_seen', false)
            ->update(['is_seen' => true]);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    public function sendMessageAsAdmin(Request $request, $user_id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $chat = Chat::create([
            'user_id' => $user_id,
            'admin_id' => auth('admin')->id(),
            'message' => $request->message,
            'sender' => 'admin',
            'is_seen' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $chat
        ]);
    }


    public function markMessagesAsSeen($user_id)
    {
        Chat::where('user_id', $user_id)
            ->where('sender', 'user')
            ->where('is_seen', false)
            ->update(['is_seen' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All user messages marked as seen.'
        ]);
    }

    
}
