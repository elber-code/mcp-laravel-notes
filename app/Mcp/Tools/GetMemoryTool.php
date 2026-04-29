<?php

namespace App\Mcp\Tools;

use App\Models\KeyNote;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Retrieves the special "memory" key note for the authenticated user. ' .
    'This is a shortcut tool to quickly access stored assistant memory or user preferences.'
)]
class GetMemoryTool extends Tool
{
    public function handle(Request $request): Response
    {
        $note = KeyNote::where('user_id', $request->user()->id)
            ->where('key', 'memory')
            ->first();

        if (! $note) {
            return Response::error("Memory note not found. The user does not have a note with key 'memory'.");
        }

        return Response::text(json_encode([
            'id'         => $note->id,
            'key'        => $note->key,
            'title'      => $note->title,
            'content'    => $note->content,
            'updated_at' => $note->updated_at->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
