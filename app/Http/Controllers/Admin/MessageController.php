<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Message::nonArchives()->orderByDesc('created_at');

        if ($request->has('non_lu')) {
            $query->where('lu', false);
        }

        $messages = $query->paginate($request->get('per_page', 20));

        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function show(Message $message): JsonResponse
    {
        if (!$message->lu) {
            $message->update(['lu' => true]);
        }

        return response()->json(['success' => true, 'data' => $message]);
    }

    public function marquerLu(Message $message): JsonResponse
    {
        $message->update(['lu' => !$message->lu]);

        return response()->json([
            'success' => true,
            'message' => $message->lu ? 'Message marqué comme lu.' : 'Message marqué comme non lu.',
            'data' => $message,
        ]);
    }

    public function destroy(Message $message): JsonResponse
    {
        $message->update(['archive' => true]);

        return response()->json(['success' => true, 'message' => 'Message archivé.']);
    }
}