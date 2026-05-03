<?php

namespace App\Mcp\Tools;

use App\Models\KeyNote;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Creates a new key note. Key notes are identified by a unique string key. ' .
    'Useful for storing specific pieces of information like memory, preferences, or settings.'
)]
class CreateKeyNoteTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'key'     => 'required|string',
            'title'   => 'nullable|string',
            'content' => 'required|string',
            'tags'    => 'nullable|array',
        ]);

        $userId = $request->user()->id;

        if (KeyNote::where('user_id', $userId)->where('key', $data['key'])->exists()) {
            return Response::error("A key note with key \"{$data['key']}\" already exists.");
        }

        $tags = collect($data['tags'] ?? [])
            ->map(fn($t) => trim(strtolower($t)))
            ->filter()
            ->values()
            ->toArray();

        // Sync tags
        foreach ($tags as $tagName) {
            \App\Models\Tag::firstOrCreate([
                'user_id' => $userId,
                'name'    => $tagName
            ]);
        }

        $note = KeyNote::create([
            'user_id' => $userId,
            'key'     => $data['key'],
            'title'   => $data['title'] ?? null,
            'content' => $data['content'],
            'tags'    => $tags,
        ]);

        return Response::text(json_encode([
            'id'         => $note->id,
            'key'        => $note->key,
            'title'      => $note->title,
            'content'    => $note->content,
            'created_at' => $note->created_at->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'key' => $schema->string()
                ->description('Unique string identifier for this note (e.g. "memory", "settings").')
                ->required(),

            'title' => $schema->string()
                ->description('Optional title.'),

            'content' => $schema->string()
                ->description('Content of the note.')
                ->required(),

            'tags' => $schema->array()
                ->items($schema->string())
                ->description('Optional array of tags.'),
        ];
    }
}
