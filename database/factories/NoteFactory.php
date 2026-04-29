<?php

namespace Database\Factories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'title'   => fake()->sentence(rand(3, 8)),
            'content' => fake()->paragraphs(rand(2, 5), true),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
