<?php

namespace App\Mcp\Tools;

use App\Models\Note;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Retrieves recently created timeline notes. ' .
    'Returns the notes created in the last X days, ordered newest first.'
)]
class GetRecentNotesTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
            'tags' => 'nullable|array',
        ]);

        $days = $data['days'] ?? 6;

        $query = Note::where('user_id', $request->user()->id)
            ->where('created_at', '>=', now()->subDays($days)->startOfDay());

        if (!empty($data['tags'])) {
            $query->where(function ($q) use ($data) {
                foreach ($data['tags'] as $tag) {
                    $q->orWhereJsonContains('tags', trim(strtolower($tag)));
                }
            });
        }

        $notes = $query->latest()
            ->get(['id', 'title', 'content', 'tags', 'created_at']);

        return Response::text(json_encode([
            'days_requested' => $days,
            'total_found'    => $notes->count(),
            'notes'          => $notes->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'content'    => $n->content,
                'tags'       => $n->tags,
                'created_at' => $n->created_at->toISOString(),
            ])->values(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'days' => $schema->integer()
                ->description('Number of days to look back. Defaults to 6.'),
            'tags' => $schema->array()
                ->items($schema->string())
                ->description('Filter by specific tags (optional). Returns notes that have at least one of these tags.'),
        ];
    }
}
