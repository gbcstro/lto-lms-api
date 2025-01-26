<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;
    public function definition(): array
    {
    	return [
    	    'title' => $this->faker->sentence(), // Generates a random title
            'description' => $this->faker->paragraph(), // Generates a random description
            'image' => $this->faker->imageUrl(), // Generates a random image URL
    	];
    }
}
