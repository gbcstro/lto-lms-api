<?php

namespace Database\Factories;

use App\Models\Choice;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChoiceFactory extends Factory
{
    protected $model = Choice::class;

    public function definition(): array
    {
    	return [
    	    'context' => $this->faker->word(), // Random choice context
            'is_correct' => $this->faker->boolean(), // Random boolean for correctness
            'question_id' => Question::inRandomOrder()->first()->id, // Random question ID
    	];
    }
}
