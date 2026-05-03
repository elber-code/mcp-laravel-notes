<?php

namespace App\Mcp\Tools;

use App\Models\KeyNote;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Edits an existing key note using its unique string key as reference. ' .
    'Useful for updating stored information like memory or preferences.'
)]
class EditKeyNoteTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'key'     => 'required|string',
            'title'   => 'nullable|string',
            'content' => 'required|string',
            'tags'    => 'nullable|array',
        ]);

        $note = KeyNote::where('user_id', $request->user()->id)
            ->where('key', $data['key'])
            ->first();

        if (! $note) {
            return Response::error("Key note with key \"{$data['key']}\" not found.");
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
            'key'        => $note->key,
            'title'      => $note->title,
            'content'    => $note->content,
            'updated_at' => $note->updated_at->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'key' => $schema->string()
                ->description('The unique string key of the note to edit.')
                ->required(),

            'title' => $schema->string()
                ->description('New optional title.'),

            'content' => $schema->string()
                ->description('New content for the note.')
                ->required(),

            'tags' => $schema->array()
                ->items($schema->string())
                ->description('New array of tags (optional).'),
        ];
    }
}
