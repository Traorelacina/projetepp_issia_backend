<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'telephone' => 'nullable|string|max:20',
            'sujet' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        $message = Message::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Votre message a été envoyé. Nous vous répondrons dans les meilleurs délais.',
        ], 201);
    }
}