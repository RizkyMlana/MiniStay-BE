<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/user/chat/send",
 *     tags={"User - Chat"},
 *     summary="User mengirim pesan ke admin",
 *     description="Mengirim pesan chat dari user ke admin.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"message"},
 *             @OA\Property(property="message", type="string", example="Halo admin, saya mau tanya...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pesan terkirim",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", ref="#/components/schemas/Chat")
 *         )
 *     )
 * )
 */

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



/**
 * @OA\Get(
 *     path="/api/user/chat",
 *     tags={"User - Chat"},
 *     summary="Ambil semua chat user",
 *     description="Menampilkan semua pesan chat user dan auto mark pesan admin sebagai 'seen'.",
 *     @OA\Response(
 *         response=200,
 *         description="Daftar chat user",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Chat")
 *             )
 *         )
 *     )
 * )
 */

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

/**
 * @OA\Get(
 *     path="/api/admin/chats/active-users",
 *     tags={"User - Chat"},
 *     summary="List user yang pernah chat",
 *     description="Menampilkan semua user yang pernah mengirim pesan.",
 *     @OA\Response(
 *         response=200,
 *         description="Daftar user aktif",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="user_id", type="integer", example=3),
 *                     @OA\Property(property="user", ref="#/components/schemas/User")
 *                 )
 *             )
 *         )
 *     )
 * )
 */


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
/**
 * @OA\Get(
 *     path="/api/admin/chats/{user_id}",
 *     tags={"Admin - Chat"},
 *     summary="Ambil semua chat dengan user tertentu",
 *     description="Admin mengambil seluruh percakapan dengan user.",
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID user",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Daftar pesan user",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Chat")
 *             )
 *         )
 *     )
 * )
 */


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

/**
 * @OA\Post(
 *     path="/api/admin/chats/{user_id}/send",
 *     tags={"Admin - Chat"},
 *     summary="Admin mengirim pesan ke user",
 *     description="Mengirim pesan chat dari admin kepada user.",
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID user",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"message"},
 *             @OA\Property(property="message", type="string", example="Halo user, ada yang bisa kami bantu?")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pesan terkirim",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", ref="#/components/schemas/Chat")
 *         )
 *     )
 * )
 */


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

/**
 * @OA\Put(
 *     path="/api/admin/chats/{user_id}/mark-seen",
 *     tags={"Admin - Chat"},
 *     summary="Mark pesan user sebagai sudah dibaca",
 *     description="Menandai semua pesan user sebagai 'seen' oleh admin.",
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID user",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Berhasil ditandai",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="All user messages marked as seen.")
 *         )
 *     )
 * )
 */


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
