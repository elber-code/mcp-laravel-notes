<?php

namespace App\Mcp\Resources;

use App\Models\KeyNote;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('User Memory')]
#[Description('The primary structured memory note containing consolidated context about the user.')]
#[Uri('memory://core')]
class MemoryResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $note = KeyNote::where('user_id', $request->user()->id)
            ->where('key', 'memory')
            ->first();

        if (! $note) {
            return Response::error("Memory note not found.");
        }

        return Response::json([
            'key'        => $note->key,
            'title'      => $note->title,
            'content'    => $note->content,
            'updated_at' => $note->updated_at->toISOString(),
        ]);

    }
}
