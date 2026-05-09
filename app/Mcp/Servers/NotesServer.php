<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\SummarizeTimelinePrompt;
use App\Mcp\Resources\MemoryResource;
use App\Mcp\Resources\RecentTimelineResource;
use App\Mcp\Resources\TagsResource;
use App\Mcp\Tools\CreateKeyNoteTool;
use App\Mcp\Tools\CreateNoteTool;
use App\Mcp\Tools\EditKeyNoteTool;
use App\Mcp\Tools\EditMemoryTool;
use App\Mcp\Tools\EditNoteTool;
use App\Mcp\Tools\GetAllTagsTool;
use App\Mcp\Tools\GetLastKeyNotesTool;
use App\Mcp\Tools\GetMemoryTool;
use App\Mcp\Tools\GetMonthNotesTool;
use App\Mcp\Tools\GetRecentNotesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Notes Server')]
#[Version('2.1.0')]
#[Instructions(
    "MCP server for managing personal notes and maintaining an AI-driven memory. " .
    "Workflow: The user frequently dictates raw, timestamped thoughts via mobile/Mac shortcuts into Timeline Notes. " .
    "Your main task as an AI is to synthesize these raw thoughts and maintain an up-to-date, structured summary in the 'memory' Key Note. " .
    "Resources: Use 'timeline://recent/{days}' to read raw logs and 'memory://core' to read the current memory state. " .
    "Organization: Use the 'tags://all' resource to discover existing categories before tagging new notes."
)]
class NotesServer extends Server
{
    /**
     * Tools registered in this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [
        // Timeline Notes
        CreateNoteTool::class,
        EditNoteTool::class,
        GetRecentNotesTool::class,
        GetMonthNotesTool::class,
        GetAllTagsTool::class,

        // Key Notes
        CreateKeyNoteTool::class,
        EditKeyNoteTool::class,
        GetMemoryTool::class,
        EditMemoryTool::class,
        GetLastKeyNotesTool::class,
    ];

    /**
     * Resources registered in this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        TagsResource::class,
        MemoryResource::class,
        RecentTimelineResource::class,
    ];

    /**
     * Prompts registered in this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        SummarizeTimelinePrompt::class,
    ];
}

