<?php

namespace App\Mcp\Resources;

use App\Models\Tag;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('Available Tags')]
#[Description('Provides a list of all existing tags in the user\'s library to help with categorization.')]
#[Uri('tags://all')]
class TagsResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $tags = Tag::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return Response::json(['tags' => $tags]);

    }
}
