<?php

use App\Mcp\Servers\NotesServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

// MCP — protegido con Sanctum Bearer token, sin CSRF (rutas API)
Mcp::web('/mcp/notes', NotesServer::class)
    ->middleware(['auth:sanctum']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
