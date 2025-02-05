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
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class GoogleSheetToDB extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        try {
            DB::transaction(function () {
                // Define CSV file paths
                $csvFiles = [
                    'Module' => storage_path('app/MODULES.csv'),
                    'ACTIVITY' => storage_path('app/ACTIVITY.csv'),
                    'LESSON' => storage_path('app/LESSON.csv'),
                    'QUESTION' => storage_path('app/QUESTION.csv'),
                    'CHOICES' => storage_path('app/CHOICES.csv'),
                ];

                // Define the models corresponding to each CSV
                $models = [
                    'Module' => Module::class,
                    'ACTIVITY' => Activity::class,
                    'LESSON' => Lesson::class,
                    'QUESTION' => Question::class,
                    'CHOICES' => Choice::class,
                ];

                foreach ($csvFiles as $sheetName => $filePath) {
                    // Open the CSV file
                    if (($handle = fopen($filePath, 'r')) !== false) {
                        // Read the header row
                        $header = fgetcsv($handle);

                        // Remove BOM (Byte Order Mark) if present
                        $header = array_map(function($value) {
                            return preg_replace('/^\xEF\xBB\xBF/', '', $value); // Remove BOM characters
                        }, $header);

                        // Convert headers to lowercase
                        $header = array_map('strtolower', $header);

                        // Loop through each row and insert into database
                        while (($row = fgetcsv($handle)) !== false) {
                            if (empty(array_filter($row))) {
                                continue; // Skip empty rows
                            }

                            $data = array_combine($header, $row); // Map columns to headers

                            // Remove null values
                            $array = array_filter($data, function($value) {
                                return $value !== null && $value !== '';
                            });

                            $model = $models[$sheetName];

                            // Convert 'is_correct' value to boolean (TRUE => true, FALSE => false)
                            if (isset($array['is_correct'])) {
                                $array['is_correct'] = strtolower($array['is_correct']) === 'true'; // Convert to boolean
                            }

                            if (isset($array['link']) && $array['link'] !== '') {
                                $link = $this->convertDriveLinkToDirectImageUrl($array['link']);
                                if ($model === Choice::class) {
                                    $array['context'] = $link;
                                } else {
                                    $array['image'] = $link;
                                }
                            }

                            unset($array['link']);

                            Log::info($array);
                            // Insert data into the appropriate model
                            $model::create($array);
                            Log::info('Created record in ' . $sheetName);
                        }
                        fclose($handle); // Close the file after processing
                    } else {
                        Log::error("Unable to open CSV file at $filePath");
                    }
                }
            });
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Convert Google Drive link to direct image URL.
     *
     * @param string $link
     * @return string
     */
    private function convertDriveLinkToDirectImageUrl(string $link): string
    {
        // Match the pattern for Google Drive file links
        if (preg_match('/drive\.google\.com\/open\?id=([a-zA-Z0-9_-]+)/', $link, $matches)) {
            $fileId = $matches[1];
            // Construct direct image URL
            return "https://drive.google.com/thumbnail?id=" . $fileId;
        }

        // Return the original link if it's not a Google Drive link
        return $link;
    }
}
