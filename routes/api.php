<?php

use App\Mcp\Servers\NotesServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

// MCP — protegido con Sanctum Bearer token, sin CSRF (rutas API)
Mcp::web('/mcp/notes', NotesServer::class)
    ->middleware(['auth:sanctum']);

// API endpoint for creating notes programmatically
Route::post('/notes', function (Request $request) {
    // Requires Jetstream 'create' permission
    if (! $request->user()->tokenCan('create')) {
        abort(403, 'This token does not have create permissions.');
    }

    $request->validate([
        'title' => 'nullable|string|max:255',
        'content' => 'required|string',
    ]);

    $title = $request->input('title') ?: now()->translatedFormat('d M Y, H:i');

    $note = $request->user()->notes()->create([
        'title' => $title,
        'content' => $request->input('content'),
    ]);

    return response()->json($note, 201);
})->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
