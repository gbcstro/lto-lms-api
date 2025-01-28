<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
    	return [
    	    'question' => $this->faker->sentence(), // Random question
            'image' => $this->faker->imageUrl(),  // Random image
            'type' => $this->faker->randomElement(['text', 'image']), // Random question type
            'activity_id' => Activity::inRandomOrder()->first()->id, // Random activity ID
    	];
    }
}
