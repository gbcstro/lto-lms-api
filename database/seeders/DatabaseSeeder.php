<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Choice;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {   
         // Create 5 modules
         Module::factory(3)->create()->each(function ($module) {
            // Create 3 lessons for each module
            Lesson::factory(5)->create(['module_id' => $module->id]);

            // Create 2 activities for each module
            Activity::factory(1)->create(['module_id' => $module->id])->each(function ($activity) {
                // Create 3 questions for each activity
                Question::factory(100)->create(['activity_id' => $activity->id])->each(function ($question) {
                    // Create 4 choices for each question
                    Choice::factory(6)->create(['question_id' => $question->id]);
                });
            });
        });
    }
}
