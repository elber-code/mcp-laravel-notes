<?php

namespace App\Mcp\Tools;

use App\Models\KeyNote;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Edits the special "memory" key note for the authenticated user. ' .
    'This is a shortcut tool to quickly update stored assistant memory or user preferences.'
)]
class EditMemoryTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'content' => 'required|string',
            'title'   => 'nullable|string',
        ]);

        $note = KeyNote::where('user_id', $request->user()->id)
            ->where('key', 'memory')
            ->first();

        if (! $note) {
            // If the memory note doesn't exist, we create it instead of throwing an error
            $note = KeyNote::create([
                'user_id' => $request->user()->id,
                'key'     => 'memory',
                'title'   => $data['title'] ?? 'Core Memory',
                'content' => $data['content'],
            ]);
        } else {
            // Update the existing note
            $note->update([
                'title'   => array_key_exists('title', $data) ? $data['title'] : $note->title,
                'content' => $data['content'],
            ]);
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
        return [
            'content' => $schema->string()
                ->description('The new memory content.')
                ->required(),

            'title' => $schema->string()
                ->description('Optional title for the memory note.'),
        ];
    }
}
