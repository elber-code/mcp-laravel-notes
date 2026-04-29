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

### 2. Key Notes (`KeyNote` model)
Specialized notes identified by a unique string key. Useful for storing preferences, assistant memory, or settings.
- **Fields:** `key` (unique per user), `title` (optional), `content`, `created_at`.
- **Querying:** Based on the string `key` or by latest created.
- **MCP Tools:**
  - `create-key-note`: Creates a new note with a specific `key`.
  - `edit-key-note`: Edits an existing note referencing its `key`.
  - `get-memory`: Shortcut tool to fetch the note with the key `'memory'`.
  - `get-last-key-notes`: Retrieves the latest X key notes created.

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

3. Run migrations and seed the database (this will create an admin user and sample notes):
   ```bash
   php artisan migrate:fresh --seed
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
        "@smithery/cli",
        "run",
        "proxy",
        "--url",
        "http://127.0.0.1:8000/api/mcp/notes",
        "--header",
        "Authorization=Bearer <your_token>"
      ]
    }
  }
}
```
*(Note: Since this is an HTTP API protected by Sanctum, using a proxy adapter like Smithery is usually required for desktop clients that only support local command execution).*

## 🛠 Testing with Inspector

You can also test the MCP tools visually using the MCP Inspector:
```bash
npx @modelcontextprotocol/inspector
```
*Note: In the inspector, set the URL to `http://127.0.0.1:8000/api/mcp/notes` and add the custom `Authorization` header with your generated Bearer token.*
