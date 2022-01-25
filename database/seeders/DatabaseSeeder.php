<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use App\Models\Option;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        DB::table('roles')->insert([
            ['id' => 1, 'description' => 'Intendent Parent'],
            ['id' => 2, 'description' => 'Surrogate'],
            ['id' => 3, 'description' => 'Administrator']
        ]);

        DB::table('types')->insert([
            ['id' => 1, 'description' => 'Intendent Parent'],
            ['id' => 2, 'description' => 'Surrogate'],
        ]);

        DB::table('sexes')->insert([
            ['id' => 1, 'description' => 'Man'],
            ['id' => 2, 'description' => 'Woman'],
            ['id' => 3, 'description' => 'N/A']
        ]);

        DB::table('maritals')->insert([
            ['id' => 1, 'description' => 'Single'],
            ['id' => 2, 'description' => 'Married'],
            ['id' => 3, 'description' => 'Relationship']
        ]);

        DB::table('question_types')->insert([
            ['id' => 1, 'description' => 'Single'],
            ['id' => 2, 'description' => 'Multiple'],
            ['id' => 3, 'description' => 'Open']
        ]);

        //QUESTIONS
        //Questions intendent type 1
        $questions_type_1 = [
            [
                "type_id" => 1,
                "title" => "How many embryos do you currently have?",
                "question_type" => 1,
                "position" => 1,
                "options" => [
                    "0",
                    "1",
                    "2-3",
                    "4-6",
                    "7+"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "How many embryos are PGS tested normal?",
                "question_type" => 1,
                "position" => 2,
                "options" => [
                    "Unknown",
                    "0",
                    "1",
                    "2",
                    "3",
                    "4+"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "How many embryos wanting to transfer?",
                "question_type" => 1,
                "position" => 3,
                "options" => [
                    "Up to 1",
                    "2",
                    "3"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "How do you feel about termination?",
                "question_type" => 1,
                "position" => 4,
                "options" => [
                    "Will never terminate",
                    "Might terminate"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "How do you feel about selective reduction?",
                "question_type" => 1,
                "position" => 5,
                "options" => [
                    "Will never reduce",
                    "Open to reducing"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "Communication preference?",
                "question_type" => 1,
                "position" => 6,
                "options" => [
                    "Little communication (check in for ultrasounds)",
                    "moderate communication (weekly text, checkin)",
                    "lots of communication (texting every day, facetimes)"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "Compensation range?",
                "question_type" => 1,
                "position" => 7,
                "options" => [
                    "Altruistic",
                    "10,000 – 20,000",
                    "20,001 – 25,000",
                    "25,001 – 30,000",
                    "30,001 – 35,000",
                    "35,001 – 40,000",
                    "more than 40,000"
                ]
            ],
            [
                "type_id" => 1,
                "title" => "Insurance coverage requirement?",
                "question_type" => 1,
                "position" => 8,
                "options" => [
                    "Absolutely necessary",
                    "not necessary (will purchase separately or out of pocket)"
                ]
            ]
        ];


        foreach ($questions_type_1 as $q) {
            $question = Question::create([
                'type_id' => $q['type_id'],
                'title' => $q['title'],
                'question_type_id' => $q['question_type'],
                'position' => $q['position']
            ]);
            foreach ($q['options'] as $option) {
                Option::create([
                    'title' => $option,
                    'question_id' => $question->id
                ]);
            }
        }

        //Questions Surrogate type 2
        $questions_type_2 = [
            [
                "type_id" => 2,
                "title" => "Occupation",
                "question_type" => 3,
                "position" => 1,
                "options" => [
                    "input"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Have you carried at least 1 child w/out complications?",
                "question_type" => 1,
                "position" => 2,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Have you had more than 3 deliveries via c-section?",
                "question_type" => 1,
                "position" => 3,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Do you have a body mass index less than 33?",
                "question_type" => 1,
                "position" => 4,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Do you smoke, abuse drugs, alcohol or prescription medications?",
                "question_type" => 1,
                "position" => 5,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Are you receiving welfare, public housing or cash assistance from the government?",
                "question_type" => 1,
                "position" => 6,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Do you have a stable home life with emotional and childcare support?",
                "question_type" => 1,
                "position" => 13,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Any mental health conditions requiring the use of meds within the last 6 months (including depression)?",
                "question_type" => 1,
                "position" => 14,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Criminal record?",
                "question_type" => 1,
                "position" => 15,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Are you working with an agency?",
                "question_type" => 1,
                "position" => 16,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Which agency?",
                "question_type" => 3,
                "position" => 17,
                "options" => [
                    "input",
                    "Rather not disclose"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "How much are your agency fees?",
                "question_type" => 3,
                "position" => 18,
                "options" => [
                    "input"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Are you willing to work with an agency?",
                "question_type" => 1,
                "position" => 19,
                "options" => [
                    "Yes",
                    "No"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "How many children do you have?",
                "question_type" => 1,
                "position" => 20,
                "options" => [
                    "1",
                    "2",
                    "3",
                    "4",
                    "5+",
                ]
            ],
            [
                "type_id" => 2,
                "title" => "How many times have you been a surrogate?",
                "question_type" => 1,
                "position" => 21,
                "options" => [
                    "None",
                    "Once",
                    "Twice",
                    "More than twice"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "What kind of families are you willing to work with (click all that apply)",
                "question_type" => 2,
                "position" => 22,
                "options" => [
                    "All",
                    "Heterosexual only",
                    "Homosexual (male) only",
                    "Homosexual (female) only",
                    "Single only"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Up to how many embryos are you willing to transfer?",
                "question_type" => 1,
                "position" => 23,
                "options" => [
                    "Up to 1",
                    "2",
                    "3"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "How do you feel about termination?",
                "question_type" => 1,
                "position" => 24,
                "options" => [
                    "Will never terminate",
                    "Might terminate"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "How do you feel about selective reduction?",
                "question_type" => 1,
                "position" => 25,
                "options" => [
                    "Will never reduce",
                    "Open to reducing"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Communication preference?",
                "question_type" => 1,
                "position" => 26,
                "options" => [
                    "Little communication (check in for ultrasounds)",
                    "moderate communication (weekly text, checkin)",
                    "lots of communication (texting every day, facetimes)"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Compensation range?",
                "question_type" => 1,
                "position" => 27,
                "options" => [
                    "Open to Altruistic",
                    "10,000 – 20,000",
                    "20,001 – 25,000",
                    "25,001 – 30,000",
                    "30,001 – 35,000",
                    "35,001 – 40,000",
                    "more than 40,000"
                ]
            ],
            [
                "type_id" => 2,
                "title" => "Insurance coverage requirement?",
                "question_type" => 1,
                "position" => 28,
                "options" => [
                    "Absolutely necessary",
                    "not necessary (will purchase separately or out of pocket)"
                ]
            ]
        ];

        foreach ($questions_type_2 as $q) {
            $question = Question::create([
                'type_id' => $q['type_id'],
                'title' => $q['title'],
                'question_type_id' => $q['question_type'],
                'position' => $q['position']
            ]);
            foreach ($q['options'] as $option) {
                Option::create([
                    'title' => $option,
                    'question_id' => $question->id
                ]);
            }
        }
    }
}
