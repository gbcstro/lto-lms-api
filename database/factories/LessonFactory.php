<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
    	return [
    	    'title' => $this->faker->sentence(), // Random title
            'content' => $this->faker->paragraph(), // Random content
            'module_id' => Module::inRandomOrder()->first()->id, // Random module ID
    	];
    }
}
