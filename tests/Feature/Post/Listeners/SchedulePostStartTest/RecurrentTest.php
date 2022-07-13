<?php

namespace Post\Listeners\SchedulePostStartTest;

use App\Events\Post\ShouldEndPost;
use App\Helpers\DateAndTimeHelper;
use App\Jobs\Post\StartPost;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrentTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    private int $displaysAmount = 3;

    private array $eventTimes
        = [
            ['start' => '15:14:00', 'end' => '15:16:00'],
            ['start' => '14:14:00', 'end' => '15:16:00'],
            ['start' => '15:14:00', 'end' => '16:16:00'],
            ['start' => '05:10:00', 'end' => '04:10:00'],
            // start one day, finishes tomorrow
            ['start' => '16:14:00', 'end' => '16:13:00'],
            // start one day, finishes tomorrow
            ['start' => '15:14:00', 'end' => '15:13:00'],
            // start one day, finishes tomorrow
        ];

    private array $recurrences
        = [
            [
                'Day' => [
                    'Day = 1'  => [
                        'recurrence' => ['day' => 1],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-02-01'
                            ],
                            [
                                'nowDate'      => '2022-05-01',
                                'scheduleDate' => '2022-06-01'
                            ],
                            [
                                'nowDate'      => '2022-12-01',
                                'scheduleDate' => '2023-01-01'
                            ],
                        ]
                    ],
                    'Day = 31' => [
                        'recurrence' => ['day' => 31],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-31',
                                'scheduleDate' => '2022-03-31'
                            ],
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-01-31'
                            ],
                            [
                                'nowDate'      => '2022-02-28',
                                'scheduleDate' => '2022-03-31'
                            ],
                            [
                                'nowDate'      => '2022-05-31',
                                'scheduleDate' => '2022-07-31'
                            ],
                        ]
                    ],
                    'Day = 5'  => [
                        'recurrence' => ['day' => 5],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-08',
                                'scheduleDate' => '2022-02-05'
                            ],
                            [
                                'nowDate'      => '2022-05-05',
                                'scheduleDate' => '2022-06-05'
                            ],
                        ]
                    ],
                ]
            ],
            [
                'IsoWeekday' => [
                    'IsoWeekday = 1' => [
                        'recurrence' => ['isoweekday' => 1],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-01-03'
                            ],
                            [
                                'nowDate'      => '2022-01-03',
                                'scheduleDate' => '2022-01-10'
                            ],
                            [
                                'nowDate'      => '2022-02-28',
                                'scheduleDate' => '2022-03-07'
                            ],
                        ]
                    ],
                    'IsoWeekday = 7' => [
                        'recurrence' => ['isoweekday' => 7],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01', // Saturday
                                'scheduleDate' => '2022-01-02' // Next monday
                            ],
                            [
                                'nowDate'      => '2022-05-04', // Wednesday
                                'scheduleDate' => '2022-05-08' // Next monday
                            ],
                            [
                                'nowDate'      => '2022-12-31', // Saturday
                                'scheduleDate' => '2023-01-01' // Next monday
                            ],
                        ]
                    ],
                ]
            ],
            [
                'Month' => [
                    'Month = 1'  => [
                        'recurrence' => ['month' => 1],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-01-02'
                            ],
                            [
                                'nowDate'      => '2022-01-30',
                                'scheduleDate' => '2022-01-31'
                            ],
                            [
                                'nowDate'      => '2022-01-31',
                                'scheduleDate' => '2023-01-01'
                            ],
                        ]
                    ],
                    'Month = 12' => [
                        'recurrence' => ['month' => 12],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-12-10',
                                'scheduleDate' => '2022-12-11'
                            ],
                            [
                                'nowDate'      => '2022-12-31',
                                'scheduleDate' => '2023-12-01'
                            ],
                        ]
                    ],
                    'Month = 2'  => [
                        'recurrence' => ['month' => 2],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-02-27', // Saturday
                                'scheduleDate' => '2022-02-28' // Next monday
                            ],
                            [
                                'nowDate'      => '2022-02-28', // Wednesday
                                'scheduleDate' => '2023-02-01' // Next monday
                            ],
                        ]
                    ],
                ]
            ],
            [
                'Year' => [
                    'Year = 2022' => [
                        'recurrence' => ['year' => 2022],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-01-02'
                            ],
                            [
                                'nowDate'      => '2022-01-03',
                                'scheduleDate' => '2022-01-04'
                            ],
                            [
                                'nowDate'      => '2022-06-15',
                                'scheduleDate' => '2022-06-16'
                            ],
                            [
                                'nowDate'      => '2022-12-30',
                                'scheduleDate' => '2022-12-31'
                            ],
                        ]
                    ],
                    'Year = 2023' => [
                        'recurrence' => ['year' => 2023],
                        'assertions' => [
                            [
                                'nowDate'      => '2023-01-01',
                                'scheduleDate' => '2023-01-02'
                            ],
                            [
                                'nowDate'      => '2023-02-27',
                                'scheduleDate' => '2023-02-28'
                            ],
                            [
                                'nowDate'      => '2023-01-03',
                                'scheduleDate' => '2023-01-04'
                            ],
                            [
                                'nowDate'      => '2023-06-15',
                                'scheduleDate' => '2023-06-16'
                            ],
                            [
                                'nowDate'      => '2023-12-30',
                                'scheduleDate' => '2023-12-31'
                            ],
                        ]
                    ],
                ]
            ],
            [
                'IsoWeekday + Day' => [
                    'IsoWeekday = 01 / Day = 01' => [
                        'recurrence' => ['isoweekday' => 1, 'day' => 1],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-05',
                                'scheduleDate' => '2022-08-01'
                            ],
                            [
                                'nowDate'      => '2022-06-15',
                                'scheduleDate' => '2022-08-01'
                            ],
                            [
                                'nowDate'      => '2022-08-01',
                                'scheduleDate' => '2023-05-01'
                            ],
                            [
                                'nowDate'      => '2023-05-01',
                                'scheduleDate' => '2024-01-01'
                            ],
                        ]
                    ],
                    'IsoWeekday = 07 / Day = 05' => [
                        'recurrence' => ['isoweekday' => 7, 'day' => 5],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-05',
                                'scheduleDate' => '2022-05-05'
                            ],
                            [
                                'nowDate'      => '2022-05-05',
                                'scheduleDate' => '2022-06-05'
                            ],
                            [
                                'nowDate'      => '2022-05-05',
                                'scheduleDate' => '2023-02-05'
                            ],
                            [
                                'nowDate'      => '2023-02-05',
                                'scheduleDate' => '2023-03-05'
                            ],
                            [
                                'nowDate'      => '2023-03-05',
                                'scheduleDate' => '2023-11-05'
                            ],
                        ]
                    ],
                ]
            ],
            [
                'IsoWeekday + Month' => [
                    'IsoWeekday = 1 / Month = 01' => [
                        'recurrence' => ['isoweekday' => 1, 'month' => 1],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01', // Saturday
                                'scheduleDate' => '2022-01-03' // Monday
                            ],
                            [
                                'nowDate'      => '2022-01-03', // Monday
                                'scheduleDate' => '2022-01-10' // Next Monday
                            ],
                            [
                                // Last day of January 2022
                                'nowDate'      => '2022-01-31',
                                // Next January Monday 2023
                                'scheduleDate' => '2023-01-02'
                            ],
                        ]
                    ],
                    'IsoWeekday = 7 / Month = 12' => [
                        'recurrence' => ['isoweekday' => 7, 'month' => 12],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-12-04'
                            ],
                            [
                                'nowDate'      => '2022-12-04',
                                'scheduleDate' => '2022-12-11'
                            ],
                            [
                                'nowDate'      => '2022-12-31', // Random date
                                // Next December Sunday
                                'scheduleDate' => '2023-12-03'
                            ],
                        ]
                    ],
                ]
            ],
            [
                'IsoWeekday + Year' => [
                    'IsoWeekday = 01 / Year = 2022' => [
                        'recurrence' => [
                            'isoweekday' => 1, 'year' => 2022
                        ],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-05',
                                'scheduleDate' => '2022-01-10'
                            ],
                            [
                                'nowDate'      => '2022-01-10',
                                'scheduleDate' => '2022-01-17'
                            ],
                            [
                                'nowDate'      => '2022-02-01',
                                'scheduleDate' => '2023-02-07'
                            ],
                            [
                                'nowDate'      => '2022-12-01',
                                'scheduleDate' => '2022-12-05'
                            ],
                        ],
                    ],
                    'IsoWeekday = 07 / Year = 2023' => [
                        'recurrence' => ['isoweekday' => 7, 'year' => 2023],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-05',
                                'scheduleDate' => '2023-01-01'
                            ],
                            [
                                'nowDate'      => '2023-01-02',
                                'scheduleDate' => '2023-01-08'
                            ],
                        ]
                    ],
                ]
            ],
            [
                'IsoWeekday + Day + Month' => [
                    'IsoWeekday = 1 / Month = 01 / Day = 01' => [
                        'recurrence' => [
                            'isoweekday' => 1, 'day' => 1, 'month' => 1,
                        ],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2024-01-01'
                            ],
                            [
                                'nowDate'      => '2024-01-01',
                                'scheduleDate' => '2029-01-01'
                            ],
                        ],
                    ],
                ]
            ],
            [
                'Day + Month' => [
                    'Day = 01 / Month = 01' => [
                        'recurrence' => [
                            'day' => 1, 'month' => 1,
                        ],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2023-01-01'
                            ],
                            [
                                'nowDate'      => '2022-05-06',
                                'scheduleDate' => '2023-01-01',
                            ],
                            [
                                'nowDate'      => '2023-01-01',
                                'scheduleDate' => '2024-01-01',
                            ],
                        ],
                    ],
                    'Day = 31 / Month = 03' => [
                        'recurrence' => [
                            'day' => 31, 'month' => 3,
                        ],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-03-31'
                            ],
                            [
                                'nowDate'      => '2022-03-31',
                                'scheduleDate' => '2023-03-31',
                            ],
                        ],
                    ],
                ]
            ],
            [
                'Day + Year' => [
                    'Day = 01 / Year = 2022' => [
                        'recurrence' => [
                            'day' => 1, 'year' => 2022,
                        ],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-01-01',
                                'scheduleDate' => '2022-02-01'
                            ],
                            [
                                'nowDate'      => '2022-02-11',
                                'scheduleDate' => '2022-03-01',
                            ],
                            [
                                'nowDate'      => '2022-08-28',
                                'scheduleDate' => '2022-09-01',
                            ],
                        ],
                    ],
                    'Day = 31 / Year = 2023' => [
                        'recurrence' => [
                            'day' => 31, 'year' => 2023,
                        ],
                        'assertions' => [
                            [
                                'nowDate'      => '2022-05-01',
                                'scheduleDate' => '2023-01-31'
                            ],
                            [
                                'nowDate'      => '2023-01-31',
                                'scheduleDate' => '2023-03-31',
                            ],
                        ],
                    ],
                ]
            ],

        ];

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->media = $this->_createMedia();
    }

    /**
     * @test
     * @dataProvider showDates
     */
    public function when_creating_recurrent_post_should_dispatch_ShouldStartPost(
        $recurrenceData,
        $nowDate,
        $correctScheduleDate,
        $startTime,
        $endTime,
    ) {
        Bus::fake([StartPost::class]);

        $emptyRecurrence = [
            'isoweekday' => null,
            'day'        => null,
            'month'      => null,
            'year'       => null,
        ];

        $recurrenceMakeData = [
            ...$emptyRecurrence,
            ...$recurrenceData,
        ];

        $recurrence = Recurrence::factory()->create($recurrenceMakeData);

        $post = Post::factory()->create([
            'start_time'    => $startTime,
            'end_time'      => $endTime,
            'media_id'      => $this->media->id,
            'recurrence_id' => $recurrence->id
        ]);

        $now = Carbon::createFromFormat('Y-m-d H:i:s', $nowDate);
        $this->travelTo($now);

        event(new ShouldEndPost($post));

        Bus::assertDispatched(StartPost::class, 1);
        Bus::assertDispatched(function (StartPost $job) use (
            $now,
            $endTime,
            $startTime,
            $correctScheduleDate
        ) {
            $correctScheduleNextStartDate
                = Carbon::createFromTimeString($correctScheduleDate);

            $scheduledJobDate = $now->copy()->addSecond($job->delay);

            $this->assertCorrectJobScheduleDate($correctScheduleNextStartDate,
                $scheduledJobDate);

            return !is_null($job->delay);
        });
    }

    public function showDates(): array
    {
        $test = [];
        foreach ($this->eventTimes as $eventTime) {
            foreach ($this->recurrences as $recurrenceData) {
                foreach (
                    $recurrenceData as $name => $data
                ) {
                    foreach ($data as $key => $assertionsData) {
                        foreach ($assertionsData['assertions'] as $assertion) {
                            $string
                                = "Recurrence $name - Case $key";

                            // Now date must be = endDate, so we can fake the ShouldEndPost event
                            $now = Carbon::createFromFormat('Y-m-d H:i:s',
                                $assertion['nowDate']
                                .' '.$eventTime['end']);

                            if (DateAndTimeHelper::isPostFromCurrentDayToNext(Carbon::createFromTimeString($eventTime['start']),
                                Carbon::createFromTimeString($eventTime['end']))
                            ) {
                                $now->addDay();
                            }

                            $test[$string] = [
                                'recurrence'          => $assertionsData['recurrence'],
                                'date'                => $now->format('Y-m-d H:i:s'),
                                'correctScheduleDate' => $assertion['scheduleDate']
                                    .' '.$eventTime['start'],
                                'startTime'           => $eventTime['start'],
                                'endTime'             => $eventTime['end'],
                            ];
                        }
                    }
                }
            }
        }
        return $test;
    }

}
