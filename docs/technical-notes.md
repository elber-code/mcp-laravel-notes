# Technical Notes & Troubleshooting

This document contains technical notes regarding architecture decisions, framework quirks (Laravel, Livewire, Alpine.js), and solutions to common problems encountered during development.

## 1. Livewire 3: Events inside `<x-slot>` (Headers & Layouts)

### The Problem
When placing buttons or interactive elements inside an `<x-slot name="header">` (common in Jetstream and Laravel blade templates) and attempting to use native Livewire events like `wire:click="$dispatch('event')"`, the event **does not fire** and Livewire ignores it.

### The Cause
Because it is not part of the main layout slot (`$slot`), but rather an isolated header slot (`$header`), the block is rendered in the main layout (`layouts/app.blade.php`) entirely outside the Livewire component's context. Being outside, `wire:` attributes simply do not work.

### The Solution
Since `wire:` directives don't work there, you must use Alpine.js events (`x-on:click`) instead. However, in order for Alpine.js to listen to and dispatch these events in an isolated block, **it is strictly necessary to 'lift' or initialize the context by adding `x-data`**.

1. **Initialize the context with `x-data`**: Ensure that the button's container has the `x-data` attribute to initialize Alpine.
2. **Dispatch event with Alpine**: Use `x-on:click` and `$dispatch` to emit a global event in the browser.
3. **Prevent page reloads**: If you use components like Jetstream's `<x-button>`, they render as `<button type="submit">` by default. You must force `type="button"` so it does not reload the page.

**Correct Example:**
```html
<x-slot name="header">
    <div class="flex justify-between items-center" x-data>
        <h2>My Notes</h2>
        <x-button type="button" x-on:click="$dispatch('open-create-modal')">
            Create Note
        </x-button>
    </div>
</x-slot>
```

In your Livewire component, listen to this global browser event using the `#[On]` attribute:

```php
use Livewire\Attributes\On;

#[On('open-create-modal')]
public function openModal()
{
    $this->isOpen = true;
}
```

## 2. Action Buttons & Unexpected Page Reloads

### The Problem
When trying to open a modal with a button (whether native or `<x-button>`), the page reloads instantly, causing the modal to close before it is even visible.

### The Cause
Browsers apply the `submit` behavior by default to `<button>` elements when no type is specified, especially if they believe the button is part of a form (or if using pre-built UI components that have it hardcoded).

### The Solution
Always explicitly add `type="button"` to any button whose sole purpose is executing JavaScript, interacting with Alpine.js, or triggering a Livewire event:

```html
<!-- Incorrect: Might cause a page reload -->
<button x-on:click="open()">Open</button>

<!-- Correct -->
<button type="button" x-on:click="open()">Open</button>
```

## 3. Tagging System & JSON Arrays

### Data Storage
Tags are stored as a JSON array in the `tags` column of both `notes` and `key_notes` tables. In the Eloquent models, these are cast to `array`:
```php
protected $casts = [
    'tags' => 'array',
];
```

### Consistency & Synchronization
We use a separate `tags` table to store a global catalog of tags per user. This ensures that:
1. Users can see a list of all their tags for filtering.
2. The AI assistant can query existing tags via the `get-all-tags` tool.
3. Tags are normalized (lowercase and trimmed) before being saved.

### Livewire Trait
The logic for adding, removing, and suggesting tags is centralized in `App\Livewire\Traits\HasTags.php`. This allows both standard and key notes to share the exact same UI behavior and backend logic.

## 4. MCP Server Architecture (Laravel MCP)

The project implements a Model Context Protocol (MCP) server using the `laravel/mcp` package. This allows AI agents to interact with the application data.

### Resources vs. Tools
- **Tools**: Used for actions that modify state (e.g., `create-note`, `edit-key-note`) or complex queries with many parameters.
- **Resources**: Used for providing raw data context via URIs.
    - Static resources (like `tags://all`) provide a quick way for the AI to "see" available metadata.
    - Dynamic resources (like `timeline://recent/{days}`) use URI templates to allow the AI to fetch specific contexts.

### Prompt Templates
Prompts are predefined instruction sets that can be triggered by the user in the AI client. They help standardize complex workflows, such as the **"Summarize Timeline"** flow, which orchestrates reading recent raw logs and updating the structured memory.

### Prompt Response Format (Role-based Messages)
The `handle()` method in a Prompt should return an **array** of role-based messages, not a single `Response`. Use `->asAssistant()` to mark the system/context message:

```php
public function handle(Request $request): array
{
    $days = $request->get('days', 7);
    return [
        Response::text("You are an expert assistant. Summarize the last {$days} days of notes.")->asAssistant(),
        Response::text("Please read timeline://recent/{$days} and update memory://core."),
    ];
}
```

`SummarizeTimelinePrompt` is implemented using this array format.

### `#[Name]` and `#[Title]` Attributes
The package distinguishes between a machine-readable identifier and a human-readable label:

```php
#[Name('get-weather')]          // machine ID used by the AI to call the tool
#[Title('Get Weather Forecast')] // human-readable label shown in the UI
#[Description('Fetches...')]    // description of what it does
class GetWeatherTool extends Tool {}
```

Currently the project's tools only set `#[Description]`. Adding `#[Name]` and `#[Title]` is recommended for clarity.

### `#[MimeType]` on Resources
Resources that return JSON should declare their MIME type explicitly:

```php
#[Uri('memory://core')]
#[MimeType('application/json')]
class MemoryResource extends Resource {}
```

Currently `MemoryResource`, `TagsResource`, and `RecentTimelineResource` are missing this attribute.

### Tool Annotations
Semantic annotations help AI clients understand tool behavior and make better decisions:

```php
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsReadOnly]    // does not modify state
#[IsIdempotent]  // repeated calls have no additional effect
#[IsDestructive] // may delete or overwrite data
class GetMemoryTool extends Tool {}
```

Read-only tools in this project (`get-memory`, `get-recent-notes`, `get-month-notes`, `get-last-key-notes`, `get-all-tags`) should all carry `#[IsReadOnly]` and `#[IsIdempotent]`.

### Resource Annotations
Resources can carry audience and priority metadata:

```php
use Laravel\Mcp\Enums\Role;
use Laravel\Mcp\Server\Annotations\Audience;
use Laravel\Mcp\Server\Annotations\Priority;

#[Audience(Role::Assistant)] // this resource is meant to be consumed by the AI
#[Priority(0.9)]             // 0.0–1.0 relevance score
class MemoryResource extends Resource {}
```

### Tool JSON Output: `Response::json()` vs `Response::structured()`
All tools use `Response::json()`, which is a compact wrapper around `json_encode` that returns a `Response`:

```php
return Response::json([
    'key'        => $note->key,
    'updated_at' => $note->updated_at->toISOString(),
]);
```

`Response::structured()` exists as an alternative and attaches data as the MCP `structuredContent` field, but it returns a `ResponseFactory` (not `Response`), which requires changing every tool's return type signature. `Response::json()` keeps the type consistent and produces compact JSON without `JSON_PRETTY_PRINT` — fewer tokens for the AI to process.

**Response design principles applied:**
- Create/edit tools return only a confirmation (id or key + timestamp) — not the content the AI just sent.
- Read tools return all fields the AI needs to process or reference data later.
- `id` is kept only on timeline notes (needed to call `edit-note`). Key notes omit `id` since the AI references them by string `key`.
- `count`/`total_found` style meta-fields are renamed to `total` for consistency.

### `outputSchema()` for Tools
Tools can optionally declare the shape of their output alongside the input schema:

```php
public function outputSchema(JsonSchema $schema): array
{
    return [
        'id'         => $schema->integer()->required(),
        'title'      => $schema->string()->required(),
        'content'    => $schema->string()->required(),
        'created_at' => $schema->string()->required(),
    ];
}
```

### Conditional Registration (`shouldRegister`)
Any Tool, Resource, or Prompt can be hidden at runtime based on the request context:

```php
public function shouldRegister(Request $request): bool
{
    return $request?->user()?->hasVerifiedEmail() ?? false;
}
```

### Dependency Injection in Tools
The service container is available both in the constructor and the `handle` method:

```php
public function handle(Request $request, MyService $service): Response
{
    return Response::structured($service->getData($request->user()));
}
```

### Testing
The package provides a fluent test helper:

```php
$response = $this->tool(GetMemoryTool::class)->handle([]);
$response = $this->tool(CreateNoteTool::class)->handle(['content' => 'Test']);
```

### Security
The MCP server is exposed at `/api/mcp/notes` and is strictly protected by the `auth:sanctum` middleware. Every request must include a valid Bearer token, which automatically scopes all database queries to the authenticated user.

