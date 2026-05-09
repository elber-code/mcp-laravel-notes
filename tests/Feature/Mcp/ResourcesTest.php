<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Resources\MemoryResource;
use App\Mcp\Resources\RecentTimelineResource;
use App\Mcp\Resources\TagsResource;
use App\Mcp\Servers\NotesServer;
use App\Models\KeyNote;
use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // --- TagsResource ---

    public function test_tags_resource_returns_ok_when_no_tags_exist(): void
    {
        NotesServer::actingAs($this->user)
            ->resource(TagsResource::class)
            ->assertOk()
            ->assertSee('tags');
    }

    public function test_tags_resource_returns_existing_tags(): void
    {
        Tag::factory()->create(['user_id' => $this->user->id, 'name' => 'work']);
        Tag::factory()->create(['user_id' => $this->user->id, 'name' => 'personal']);

        NotesServer::actingAs($this->user)
            ->resource(TagsResource::class)
            ->assertOk()
            ->assertSee('work')
            ->assertSee('personal');
    }

    public function test_tags_resource_is_scoped_to_authenticated_user(): void
    {
        $other = User::factory()->create();
        Tag::factory()->create(['user_id' => $other->id, 'name' => 'other-secret-tag']);

        NotesServer::actingAs($this->user)
            ->resource(TagsResource::class)
            ->assertOk()
            ->assertDontSee('other-secret-tag');
    }

    // --- MemoryResource ---

    public function test_memory_resource_returns_error_when_no_memory_note_exists(): void
    {
        NotesServer::actingAs($this->user)
            ->resource(MemoryResource::class)
            ->assertHasErrors(['Memory note not found']);
    }

    public function test_memory_resource_returns_ok_when_memory_note_exists(): void
    {
        KeyNote::factory()->create([
            'user_id' => $this->user->id,
            'key'     => 'memory',
            'content' => 'Structured memory content here.',
        ]);

        NotesServer::actingAs($this->user)
            ->resource(MemoryResource::class)
            ->assertOk()
            ->assertSee('memory')
            ->assertSee('Structured memory content here.');
    }

    public function test_memory_resource_is_scoped_to_authenticated_user(): void
    {
        $other = User::factory()->create();
        KeyNote::factory()->create([
            'user_id' => $other->id,
            'key'     => 'memory',
            'content' => 'Other user private memory.',
        ]);

        NotesServer::actingAs($this->user)
            ->resource(MemoryResource::class)
            ->assertHasErrors(['Memory note not found']);
    }

    // --- RecentTimelineResource ---

    public function test_recent_timeline_resource_returns_ok(): void
    {
        Note::factory()->create([
            'user_id'    => $this->user->id,
            'content'    => 'A recent thought.',
            'created_at' => now(),
        ]);

        NotesServer::actingAs($this->user)
            ->resource(RecentTimelineResource::class)
            ->assertOk()
            ->assertSee('notes')
            ->assertSee('total');
    }

    public function test_recent_timeline_resource_returns_ok_when_empty(): void
    {
        NotesServer::actingAs($this->user)
            ->resource(RecentTimelineResource::class)
            ->assertOk()
            ->assertSee('total');
    }

    public function test_recent_timeline_resource_is_scoped_to_authenticated_user(): void
    {
        $other = User::factory()->create();
        Note::factory()->create([
            'user_id'    => $other->id,
            'content'    => 'Completely private note.',
            'created_at' => now(),
        ]);

        NotesServer::actingAs($this->user)
            ->resource(RecentTimelineResource::class)
            ->assertOk()
            ->assertDontSee('Completely private note.');
    }
}
