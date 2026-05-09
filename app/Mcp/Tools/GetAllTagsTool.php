<?php

namespace App\Mcp\Tools;

use App\Models\Tag;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description(
    'Returns a list of all unique tags created by the user. ' .
    'Useful for understanding how information is categorized.'
)]
class GetAllTagsTool extends Tool
{
    public function handle(Request $request): Response
    {
        $tags = Tag::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->pluck('name')
            ->toArray();

        return Response::json(['tags' => $tags]);
    }

    public function schema(JsonSchema $_schema): array
    {
        return [];
    }
}
