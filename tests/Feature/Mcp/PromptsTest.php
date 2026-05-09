<?php

namespace Tests\Feature\Mcp;

use App\Mcp\Prompts\SummarizeTimelinePrompt;
use App\Mcp\Servers\NotesServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromptsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_summarize_timeline_prompt_returns_ok(): void
    {
        NotesServer::actingAs($this->user)
            ->prompt(SummarizeTimelinePrompt::class, ['days' => '7'])
            ->assertOk();
    }

    public function test_summarize_timeline_prompt_contains_instructions(): void
    {
        NotesServer::actingAs($this->user)
            ->prompt(SummarizeTimelinePrompt::class, ['days' => '7'])
            ->assertOk()
            ->assertSee('memory');
    }

    public function test_summarize_timeline_prompt_interpolates_days_argument(): void
    {
        NotesServer::actingAs($this->user)
            ->prompt(SummarizeTimelinePrompt::class, ['days' => '14'])
            ->assertOk()
            ->assertSee('14');
    }

    public function test_summarize_timeline_prompt_defaults_to_seven_days_when_no_argument(): void
    {
        NotesServer::actingAs($this->user)
            ->prompt(SummarizeTimelinePrompt::class, [])
            ->assertOk()
            ->assertSee('7');
    }
}
