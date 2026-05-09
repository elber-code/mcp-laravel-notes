<?php

namespace App\Mcp\Tools;

use App\Models\KeyNote;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Retrieves the most recently created key notes. ' .
    'Useful for seeing what special information was recently stored.'
)]
class GetLastKeyNotesTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'limit' => 'nullable|integer|min:1|max:1000',
            'tags'  => 'nullable|array',
        ]);

        $limit = $data['limit'] ?? 100;

        $query = KeyNote::where('user_id', $request->user()->id);

        if (!empty($data['tags'])) {
            $query->where(function ($q) use ($data) {
                foreach ($data['tags'] as $tag) {
                    $q->orWhereJsonContains('tags', trim(strtolower($tag)));
                }
            });
        }

        $notes = $query->latest()
            ->limit($limit)
            ->get(['id', 'key', 'title', 'content', 'tags', 'created_at']);

        return Response::json([
            'total' => $notes->count(),
            'notes' => $notes->map(fn ($n) => [
                'key'        => $n->key,
                'title'      => $n->title,
                'content'    => $n->content,
                'tags'       => $n->tags,
                'created_at' => $n->created_at->toISOString(),
            ])->values(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->description('Maximum number of key notes to return. Defaults to 100.'),
            'tags' => $schema->array()
                ->items($schema->string())
                ->description('Filter by specific tags (optional). Returns key notes that have at least one of these tags.'),
        ];
    }
}
