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
