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
