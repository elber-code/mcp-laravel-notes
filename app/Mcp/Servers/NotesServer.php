<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreateKeyNoteTool;
use App\Mcp\Tools\CreateNoteTool;
use App\Mcp\Tools\EditKeyNoteTool;
use App\Mcp\Tools\EditMemoryTool;
use App\Mcp\Tools\EditNoteTool;
use App\Mcp\Tools\GetLastKeyNotesTool;
use App\Mcp\Tools\GetMemoryTool;
use App\Mcp\Tools\GetMonthNotesTool;
use App\Mcp\Tools\GetRecentNotesTool;
use App\Mcp\Tools\GetAllTagsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Notes Server')]
#[Version('2.0.0')]
#[Instructions(
    "MCP server for managing personal notes and maintaining an AI-driven memory. " .
    "Workflow: The user frequently dictates raw, timestamped thoughts via mobile/Mac shortcuts into Timeline Notes. " .
    "Your main task as an AI is to use 'get-recent-notes' to read these raw thoughts, synthesize them, " .
    "and maintain an up-to-date, structured summary using 'edit-key-note' (or create-key-note) with the key 'memory'. " .
    "Note Types: " .
    "1. Timeline Notes (raw logs). Tools: create-note, edit-note, get-recent-notes, get-month-notes. " .
    "2. Key Notes (structured data/memory). Tools: create-key-note, edit-key-note, get-memory, get-last-key-notes. " .
    "Tags: Both note types support an optional 'tags' array to categorize information."
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
}
