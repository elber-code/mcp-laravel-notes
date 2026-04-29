<?php

namespace App\Mcp\Tools;

use App\Models\Note;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Creates a new timeline note. ' .
    'Useful for daily journals or sequential logs. ' .
    'The note is automatically associated with the authenticated user.'
)]
class CreateNoteTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'title'   => 'nullable|string',
            'content' => 'required|string',
        ], [
            'content.required' => 'The note content is required.',
        ]);

        $note = Note::create([
            'user_id' => $request->user()->id,
            'title'   => $data['title'] ?? null,
            'content' => $data['content'],
        ]);

        return Response::text(json_encode([
            'id'         => $note->id,
            'title'      => $note->title,
            'content'    => $note->content,
            'created_at' => $note->created_at->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->description('Optional title for the note.'),

            'content' => $schema->string()
                ->description('Main content of the note.')
                ->required(),
        ];
    }
}
