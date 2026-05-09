<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# 📝 Notes App (with MCP Server)

This is a Laravel 11 application with Jetstream (Livewire) that serves as a personal notes system. It integrates a **Model Context Protocol (MCP)** server, allowing AI agents to interact with the user's notes securely via API.

## Documentation & Technical Notes

For architectural decisions, Livewire 3 quirks, and common troubleshooting (such as handling events inside slots and modals), please refer to the `docs/` folder:
- [Technical Notes & Troubleshooting](docs/technical-notes.md)

## 🏗 Architecture

The notes system is divided into two distinct concepts to keep data organized:

### 1. Timeline Notes (`Note` model)
Standard notes that represent chronological entries, like a daily journal or sequential log.
- **Fields:** `title` (optional), `content`, `created_at`.
- **Querying:** Based on creation date (`created_at`).
- **MCP Tools:**
  - `create-note`: Creates a new timeline note.
  - `edit-note`: Edits a note using its numerical `id`.
  - `get-recent-notes`: Returns notes from the last X days.
  - `get-month-notes`: Returns notes created in a specific month.
  - `get-all-tags`: Returns a list of all unique tags used in the system.
- **MCP Resources:**
  - `timeline://recent/{days}`: Dynamic access to the most recent timeline notes.

### 2. Key Notes (`KeyNote` model)
Specialized notes identified by a unique string key. Useful for storing preferences, assistant memory, or settings.
- **Fields:** `key` (unique per user), `title` (optional), `content`, `created_at`.
- **Querying:** Based on the string `key` or by latest created.
- **MCP Tools:**
  - `create-key-note`: Creates a new note with a specific `key`.
  - `edit-key-note`: Edits an existing note referencing its `key`.
  - `get-memory`: Shortcut tool to fetch the note with the key `'memory'`.
  - `get-last-key-notes`: Retrieves the latest X key notes created.
- **MCP Resources:**
  - `memory://core`: Direct access to the user's memory note.
  - `tags://all`: Global list of tags for categorization.

### 3. Workflows (Prompts)
The server now includes prompt templates to guide the AI in complex tasks:
- **`Summarize Timeline`**: Instructs the AI to read recent timeline notes and update the user's structured memory.


### 🏷️ Tagging System
Both note types support a tagging system. Tags are stored as a JSON array in the database and are automatically indexed for searching.
- **Filtering:** You can filter notes by selecting multiple tags on the main dashboard.
- **Persistence:** Tags are synchronized globally. Adding a new tag to a note automatically adds it to your user's tag library.
- **API Support:** Create and edit notes via API/MCP while passing an array of tags.

### 🔍 Markdown Preview & Full Screen
The application features a powerful Markdown previewer for notes.
- **Agrandar (Expand):** Every note preview can be toggled to a full-screen mode for focused reading and better visibility of long content.
- **Modern UI:** Uses `prose` classes for beautiful typography and dark mode compatibility.



## 🔐 Authentication

The MCP Server is accessible via an HTTP endpoint (`/api/mcp/notes`) and is protected using **Laravel Sanctum**.

Agents connecting to this server must provide a Bearer token in the Authorization header:
```bash
Authorization: Bearer <your_sanctum_token>
```
This token automatically identifies the user, ensuring that all created notes and retrieved data belong strictly to the authenticated user.

## 🚀 Getting Started

1. Clone the repository and install dependencies:
   ```bash
   composer install
   npm install
   ```

2. Set up your environment file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Run migrations and seed the database:
   - **For development (with random dummy notes):**
     ```bash
     php artisan migrate:fresh --seed
     ```
   - **For production (only creates Admin user and the blank `memory` key note):**
     ```bash
     php artisan migrate --force
     php artisan db:seed --class=ProductionSeeder --force
     ```

4. Generate an MCP token for the seeded user (notas@example.com, or your custom ADMIN_EMAIL from .env) to use with your AI agent:
   ```bash
   php artisan tinker --execute="echo App\Models\User::where('email', env('ADMIN_EMAIL', 'notas@example.com'))->first()->createToken('mcp')->plainTextToken;"
   ```

5. Serve the application:
   ```bash
   php artisan serve
   ```

## 🤖 Connecting to AI Clients

### In Cursor IDE
Cursor supports connecting to web-based (SSE/HTTP) MCP servers natively.
1. Open Cursor Settings > **Features** > **MCP**.
2. Click **+ Add New MCP Server**.
3. Select **SSE** (or Web) as the type.
4. Set Name: `Notes`
5. Set URL: `http://127.0.0.1:8000/api/mcp/notes`
6. Add Header: `Authorization` with value `Bearer <your_token>`.

### In Claude Desktop / Other JSON Configs
If your AI client uses a configuration file (like `claude_desktop_config.json`), and supports remote endpoints or you are using an SSE-to-STDIO proxy bridge, the configuration will look similar to this:

```json
{
  "mcpServers": {
    "laravel-notes": {
      "command": "npx",
      "args": [
        "-y",
        "supergateway",
        "--streamableHttp",
        "http://127.0.0.1:8000/api/mcp/notes",
        "--header",
        "Authorization: Bearer <your_token>"
      ]
    }
  }
}
```
*(Note: Since this is an HTTP API protected by Sanctum, using a proxy adapter like `supergateway` is usually required for desktop clients that only support local command execution).*

## 🛠 Testing & Debugging

### Unit Tests
The MCP tools, resources, and prompts have automated PHPUnit tests. Run them with:
```bash
php artisan test tests/Feature/Mcp/
```

The test suite uses `RefreshDatabase` and Laravel's built-in MCP test helpers via static calls on the server class:
```php
NotesServer::actingAs($user)->resource(MemoryResource::class)->assertOk();
NotesServer::actingAs($user)->prompt(SummarizeTimelinePrompt::class, ['days' => '7'])->assertSee('memory');
```

### Interactive Inspector
To test the MCP server's tools, resources, and prompts interactively:

#### 1. Start the Laravel Server
```bash
php artisan serve
```

#### 2. Generate an Access Token
Since the server is protected by Sanctum, you need a Bearer token:
```bash
php artisan tinker --execute="echo App\Models\User::first()->createToken('mcp-test')->plainTextToken;"
```

#### 3. Use the MCP Inspector
There are two ways to open the inspector:

**Option A: Built-in Inspector (Recommended)**
```bash
# The 'handle' is typically the last part of the route (in this case 'notes')
php artisan mcp:inspector notes
```

**Option B: Official Inspector (npx)**
```bash
npx @modelcontextprotocol/inspector http://127.0.0.1:8000/api/mcp/notes
```

*Note: Once the inspector is open, make sure to add the `Authorization` header with the value `Bearer <your_token>` in the connection settings.*


## 🎙️ Mac Shortcuts Integration (Voice Notes)

You can easily integrate the `POST /api/notes` endpoint with the Apple Shortcuts app on macOS/iOS to create a "Record Note" shortcut. This allows you to dictate a note and send it directly to your application in the background.

> **Important:** For this shortcut to work, you must generate an API Token from your profile view in the web application (**API Tokens** section) and make sure to check the **`create`** permission box (write permissions).

### How to build the Shortcut step-by-step:
1. Open the **Shortcuts** app on your Mac or iPhone and create a new shortcut.
2. Add the **Dictate Text** action (this will capture your voice and convert it to text).
3. Add the **Get Contents of URL** action.
   - **URL**: `http://127.0.0.1:8000/api/notes` (or your production domain).
   - Click the arrow for "Show More".
   - **Method**: `POST`
   - **Headers**:
     - `Authorization`: `Bearer <YOUR_TOKEN>` *(Make sure your Jetstream token has the `create` permission)*.
     - `Accept`: `application/json`
   - **Request Body**: Select `JSON`
     - Add a new field: `content` (Text) -> assign the value to the `Dictated Text` variable from step 2.

That's it! Now you can trigger this shortcut from your Mac menu bar, via Siri, or with a keyboard shortcut. When you speak, your note will be automatically saved, and since no title is provided, it will default to the current date and time.

![Mac Shortcut to Record Note](docs/imgs/mac-shortcut.png)
