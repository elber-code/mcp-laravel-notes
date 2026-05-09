<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('Summarize Timeline')]
#[Description('A prompt template to help the AI summarize recent timeline notes into the structured memory.')]
class SummarizeTimelinePrompt extends Prompt
{
    /**
     * Get the arguments for the prompt.
     *
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('days', 'Number of recent days to summarize', true),
        ];
    }

    /**
     * Handle the prompt request.
     *
     * @return array<int, Response>
     */
    public function handle(Request $request): array
    {
        $days = $request->get('days') ?? 7;

        return [
            Response::text(
                "You are an expert personal assistant. Your task is to review the timeline notes from the last {$days} days " .
                "and update the structured 'memory' note. \n\n" .
                "Steps:\n" .
                "1. Read the recent notes using the 'timeline://recent/{$days}' resource.\n" .
                "2. Read the current memory using the 'memory://core' resource.\n" .
                "3. Synthesize new information, identify patterns, and update the memory note using the 'edit-key-note' tool.\n" .
                "4. Be concise and maintain the existing structure of the memory."
            )->asAssistant(),
            Response::text("Please summarize my timeline notes from the last {$days} days and update my memory."),
        ];
    }
}
