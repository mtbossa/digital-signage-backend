<?php

namespace Post\ScheduleWhenCreating;

use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrentTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    private int $displaysAmount = 3;

    /**
     * All values here are based on chosen $nowDate;
     */

    // Between these times, must always dispatch event
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
    // Between these times, must always place in the queue
    private array $queueTimes
        = [
            ['start' => '15:16:00', 'end' => '15:17:00'],
            ['start' => '15:13:00', 'end' => '15:14:00'],
            ['start' => '16:10:00', 'end' => '15:14:00'],
        ];

    private array $recurrences
        = [
            [
                'isoWeekdayOnly' => [
                    'recurrence' => ['isoweekday' => 1],
                    'nowDates'   => [
                        'shouldShow'    => ['2022-01-03', '2022-04-04'],
                        'shouldNotShow' => ['2022-02-03', '2022-05-04'],
                    ]
                ]
            ],
            [
                'isoWeekdayWithDay' => [
                    'recurrence' => ['isoweekday' => 1, 'day' => 1],
                    'nowDates'   => [
                        'shouldShow'    => ['2022-08-01'],
                        'shouldNotShow' => ['2022-07-01', '2022-08-02'],
                    ]
                ]

            ],
            [
                'isoWeekdayWithMonth' => [
                    'recurrence' => ['isoweekday' => 1, 'month' => 1],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-03', '2022-01-10', '2022-01-17',
                            '2022-01-24', '2022-01-31', '2023-01-02',
                            '2023-01-09'
                        ],
                        'shouldNotShow' => [
                            '2022-01-01', '2022-02-07', '2023-01-03'
                        ],
                    ]
                ]
            ],
            [
                'isoWeekdayWithYear' => [
                    'recurrence' => ['isoweekday' => 1, 'year' => 2022],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-03', '2022-01-10', '2022-02-07',
                            '2022-02-14', '2022-10-03', '2022-10-10',
                        ],
                        'shouldNotShow' => [
                            '2022-01-01', '2022-01-02', '2022-02-02',
                            '2022-02-03', '2023-01-02', '2023-02-06'
                        ],
                    ]
                ]
            ],
            [
                'dayOnly' => [
                    'recurrence' => ['day' => 1],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-01', '2022-02-01', '2022-03-01',
                            '2022-06-01', '2023-01-01', '2023-02-01',
                        ],
                        'shouldNotShow' => [
                            '2022-01-02', '2022-01-03', '2022-02-02',
                            '2022-02-03', '2023-01-02', '2023-02-06'
                        ],
                    ]
                ]
            ],
            [
                'dayWithMonth' => [
                    'recurrence' => ['day' => 1, 'month' => 1],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-01', '2023-01-01', '2024-01-01',
                        ],
                        'shouldNotShow' => [
                            '2022-01-02', '2022-01-03', '2022-02-01',
                            '2022-03-01', '2023-01-02', '2023-02-01'
                        ],
                    ]
                ]
            ],
            [
                'dayWithYear' => [
                    'recurrence' => ['day' => 1, 'year' => 2022],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-01', '2022-02-01', '2022-03-01',
                            '2022-06-01',
                        ],
                        'shouldNotShow' => [
                            '2022-01-02', '2022-01-03', '2022-02-02',
                            '2022-02-03', '2023-01-01', '2023-01-02'
                        ],
                    ]
                ]
            ],
            [
                'monthOnly' => [
                    'recurrence' => ['month' => 1],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-01', '2022-01-02', '2022-01-03',
                            '2022-01-29', '2023-01-01', '2023-01-02'
                        ],
                        'shouldNotShow' => [
                            '2022-02-01', '2022-02-02', '2022-05-01',
                            '2023-02-01', '2023-02-02', '2023-05-01'
                        ],
                    ]
                ]
            ],
            [
                'monthYearYear' => [
                    'recurrence' => ['month' => 1, 'year' => 2022],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-01', '2022-01-02', '2022-01-03',
                            '2022-01-29',
                        ],
                        'shouldNotShow' => [
                            '2022-02-01', '2022-02-02', '2022-05-01',
                            '2023-01-01',
                            '2023-01-02',
                            '2023-02-01', '2023-02-02', '2023-05-01'
                        ],
                    ]
                ]
            ],
            [
                'yearOnly' => [
                    'recurrence' => ['year' => 2022],
                    'nowDates'   => [
                        'shouldShow'    => [
                            '2022-01-01', '2022-01-02', '2022-01-03',
                            '2022-01-29', '2022-02-01', '2022-02-02',
                            '2022-03-01',
                            '2022-12-31'
                        ],
                        'shouldNotShow' => [
                            '2023-01-01', '2023-01-02', '2023-05-01'
                        ],
                    ]
                ]
            ],
        ];


    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->media = $this->_createMedia();
        $this->recurrence = $this->_createRecurrence();
        $this->post = $this->_createPost([
            'media_id'      => $this->media->id,
            'recurrence_id' => $this->recurrence->id
        ]);
    }

    /**
     * @test
     * @dataProvider showDates
     */
    public function when_creating_recurrent_post_should_dispatch_ShouldStartPost(
        $recurrence,
        $nowDate,
        $startTime,
        $endTime,
    ) {
        $now = Carbon::createFromFormat('Y-m-d', $nowDate);
        $startTimeObject = Carbon::createFromTimeString($startTime);

        $now->setTimeFromTimeString($startTime)->addSecond();

        $recurrence = Recurrence::factory()->create($recurrence);
        $post = Post::factory()->create([
            'recurrence_id' => $recurrence->id, 'start_time' => $startTime,
            'end_time'      => $endTime
        ]);

    }

    public function showDates(): array
    {
        $test = [];
        foreach ($this->eventTimes as $eventTime) {
            foreach ($this->recurrences as $recurrenceData) {
                foreach (
                    $recurrenceData as $name => $data
                ) {
                    foreach ($data['nowDates']['shouldShow'] as $notShowDate) {
                        $string
                            = "Not show $name - now date: $notShowDate";

                        $test[$string] = [
                            'recurrence' => $data['recurrence'],
                            'date'       => $notShowDate,
                            'startTime'  => $eventTime['start'],
                            'endTime'    => $eventTime['end'],
                        ];
                    }
                }
            }
        }
        return $test;
    }

    /**
     * @test
     * @dataProvider notShowDates
     */
    public function when_creating_recurrent_post_should_schedule(
        $recurrence,
        $nowDate,
        $startTime,
        $endTime
    ) {
//        $this->showPostAssertion(
//            $startDate,
//            $endDate,
//            $startTime,
//            $endTime,
//            $this->nowDate,
//            $this->displaysAmount,
//            PostShouldDo::Event
//        );
    }

    public function notShowDates(): array
    {
        $test = [];
        foreach ($this->eventTimes as $eventTime) {
            foreach ($this->recurrences as $recurrenceData) {
                foreach (
                    $recurrenceData as $name => $data
                ) {
                    foreach ($data['nowDates']['shouldNotShow'] as $notShowDate)
                    {
                        $string
                            = "Not show $name - now date: $notShowDate";

                        $test[$string] = [
                            'recurrence' => $data['recurrence'],
                            'date'       => $notShowDate
                        ];
                    }
                }
            }
        }
        return $test;
    }

}
