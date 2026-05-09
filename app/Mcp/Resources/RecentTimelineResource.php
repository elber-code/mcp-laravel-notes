<?php

namespace App\Mcp\Resources;

use App\Models\Note;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Name('Recent Timeline')]
#[Description('Retrieves recently created timeline notes. Template: notes://timeline/recent/{days}')]
class RecentTimelineResource extends Resource implements HasUriTemplate
{
    /**
     * Get the URI pattern for the resource template.
     */
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('timeline://recent/{days}');
    }

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $days = (int) ($request->get('days') ?? 7);

        $notes = Note::where('user_id', $request->user()->id)
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->latest()
            ->get(['id', 'title', 'content', 'tags', 'created_at']);

        return Response::json([
            'days' => $days,
            'total' => $notes->count(),
            'notes' => $notes->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->title,
                'content'    => $n->content,
                'tags'       => $n->tags,
                'created_at' => $n->created_at->toISOString(),
            ])->values(),
        ]);

    }
}
