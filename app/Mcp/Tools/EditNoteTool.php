<?php

namespace App\Mcp\Tools;

use App\Models\Note;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Edits an existing timeline note using its numerical ID. ' .
    'You can update the title, content, and tags.'
)]
class EditNoteTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'id'      => 'required|integer',
            'title'   => 'nullable|string',
            'content' => 'required|string',
            'tags'    => 'nullable|array',
        ]);

        $note = Note::where('user_id', $request->user()->id)
            ->where('id', $data['id'])
            ->first();

        if (! $note) {
            return Response::error("Note with ID {$data['id']} not found or access denied.");
        }

        $updateData = [
            'title'   => array_key_exists('title', $data) ? $data['title'] : $note->title,
            'content' => $data['content'],
        ];

        if (array_key_exists('tags', $data)) {
            $tags = collect($data['tags'] ?? [])
                ->map(fn($t) => trim(strtolower($t)))
                ->filter()
                ->values()
                ->toArray();

            // Sync tags
            foreach ($tags as $tagName) {
                \App\Models\Tag::firstOrCreate([
                    'user_id' => $request->user()->id,
                    'name'    => $tagName
                ]);
            }
            $updateData['tags'] = $tags;
        }

        $note->update($updateData);

        return Response::text(json_encode([
            'id'         => $note->id,
            'title'      => $note->title,
            'content'    => $note->content,
            'updated_at' => $note->updated_at->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()
                ->description('Numerical ID of the note to edit.')
                ->required(),

            'title' => $schema->string()
                ->description('New title for the note (optional).'),

            'content' => $schema->string()
                ->description('New content for the note.')
                ->required(),

            'tags' => $schema->array()
                ->items($schema->string())
                ->description('New array of tags (optional).'),
        ];
    }
}
