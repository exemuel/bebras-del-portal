<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'excerpt' => $this->faker->paragraph(2),
            'content' => '<p>' . implode('</p><p>', $this->faker->paragraphs(3)) . '</p>',
            'image_path' => null,
            'is_published' => true,
            'published_at' => now()->subDays($this->faker->numberBetween(0, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->addDays(5),
        ]);
    }
}
