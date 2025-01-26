<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
    	return [
    	    'title' => $this->faker->sentence(), // Random title
            'description' => $this->faker->paragraph(), // Random description
            'module_id' => Module::inRandomOrder()->first()->id, // Random module ID
            'image' => $this->faker->imageUrl(), // Random image URL
    	];
    }
}
