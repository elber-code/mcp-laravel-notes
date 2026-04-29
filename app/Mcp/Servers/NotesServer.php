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
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Notes Server')]
#[Version('2.0.0')]
#[Instructions(
    'MCP server for managing personal notes. ' .
    'There are two types of notes: ' .
    '1. Timeline Notes (no key, ordered by created_at). Tools: create-note, edit-note, get-recent-notes, get-month-notes. ' .
    '2. Key Notes (unique string key, used for memory/preferences). Tools: create-key-note, edit-key-note, get-memory, get-last-key-notes. ' .
    'Authentication is handled via Sanctum Bearer token.'
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

        // Key Notes
        CreateKeyNoteTool::class,
        EditKeyNoteTool::class,
        GetMemoryTool::class,
        EditMemoryTool::class,
        GetLastKeyNotesTool::class,
    ];
}
