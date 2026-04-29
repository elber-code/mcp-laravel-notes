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
        ]);

        $limit = $data['limit'] ?? 100;

        $notes = KeyNote::where('user_id', $request->user()->id)
            ->latest()
            ->limit($limit)
            ->get(['id', 'key', 'title', 'content', 'created_at']);

        return Response::text(json_encode([
            'limit_requested' => $limit,
            'total_found'     => $notes->count(),
            'notes'           => $notes->map(fn ($n) => [
                'id'         => $n->id,
                'key'        => $n->key,
                'title'      => $n->title,
                'content'    => $n->content,
                'created_at' => $n->created_at->toISOString(),
            ])->values(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()
                ->description('Maximum number of key notes to return. Defaults to 100.')
        ];
    }
}
