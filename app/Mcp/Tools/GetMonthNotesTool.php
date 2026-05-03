<?php

namespace App\Mcp\Tools;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Retrieves timeline notes created in a specific month. ' .
    'The month should be provided in MM-YYYY format.'
)]
class GetMonthNotesTool extends Tool
{
    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{2}-\d{4}$/'],
            'tags'  => 'nullable|array',
        ]);

        $date = Carbon::createFromFormat('d-m-Y', '01-' . $data['month']);

        $query = Note::where('user_id', $request->user()->id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month);

        if (!empty($data['tags'])) {
            $query->where(function ($q) use ($data) {
                foreach ($data['tags'] as $tag) {
                    $q->orWhereJsonContains('tags', trim(strtolower($tag)));
                }
            });
        }

        $notes = $query->orderBy('created_at')
            ->get(['id', 'title', 'content', 'tags', 'created_at']);

        return Response::text(json_encode([
            'month'       => $data['month'],
            'total_found' => $notes->count(),
            'notes'       => $notes->map(fn ($n) => [
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
            'month' => $schema->string()
                ->description('Month in MM-YYYY format (e.g. 04-2026).')
                ->required(),
            'tags' => $schema->array()
                ->items($schema->string())
                ->description('Filter by specific tags (optional). Returns notes that have at least one of these tags.'),
        ];
    }
}
