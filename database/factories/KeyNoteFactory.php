<?php

namespace Database\Factories;

use App\Models\KeyNote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KeyNote>
 */
class KeyNoteFactory extends Factory
{
    protected $model = KeyNote::class;

    public function definition(): array
    {
        return [
            'key'     => Str::slug(fake()->unique()->words(rand(2, 4), true)),
            'title'   => fake()->sentence(rand(3, 8)),
            'content' => fake()->paragraphs(rand(2, 5), true),
            'tags'    => fake()->boolean(50) ? fake()->randomElements(['config', 'system', 'memory', 'archived'], rand(1, 2)) : null,
        ];
    }
}
