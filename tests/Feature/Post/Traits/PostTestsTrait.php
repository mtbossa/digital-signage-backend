<?php

namespace Tests\Feature\Post\Traits;

use App\Events\Post\ShouldStartPost;
use App\Jobs\Post\StartPost;
use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Post\Enums\PostShouldDo;


trait PostTestsTrait
{
    private Post $post;
    private Media $media;
    private Recurrence $recurrence;

    private function _createPost(array $data = null): Post
    {
        return Post::factory()->create($data);
    }

    private function _createRecurrence(array $data = null): Recurrence
    {
        return Recurrence::factory()->create($data);
    }

    private function showPostAssertion(
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        string $nowDateTime,
        int $amountOfDisplays,
        PostShouldDo $shouldShowOrQueue,
    ): void {
        Bus::fake();
        Event::fake();

        $this->configureEventsTests($startDate, $endDate, $startTime, $endTime,
            $nowDateTime, $amountOfDisplays);

        switch ($shouldShowOrQueue) {
            case PostShouldDo::Event:
                Event::assertDispatched(ShouldStartPost::class, 1);
                Bus::assertNotDispatched(StartPost::class);
                return;
            case PostShouldDo::Queue:
                Bus::assertDispatched(StartPost::class, 1);
                Event::assertNotDispatched(ShouldStartPost::class);
                return;
        }
    }

    private function configureEventsTests(
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        string $nowDateTime,
        int $amountOfDisplays,
    ): void {
        $this->travelTo(Carbon::createFromFormat('Y-m-d H:i:s',
            $nowDateTime));

        $media = $this->_createMedia();
        $displays_ids
            = $this->createDisplaysAndReturnIds($amountOfDisplays);
        $post_data = $this->_makePost([
            'start_date'   => $startDate, 'start_time' => $startTime,
            'end_date'     => $endDate, 'end_time' => $endTime,
            'displays_ids' => $displays_ids,
            'media_id'     => $media->id
        ], false)->toArray();

        $response = $this->postJson(route('posts.store'), $post_data);
    }

    private function _createMedia(array $data = null): Media
    {
        return Media::factory()->create($data);
    }

    private function createDisplaysAndReturnIds(int $amount): array
    {
        return Display::factory($amount)->create()->pluck(['id'])->toArray();
    }

    private function _makePost(array $data = null, bool $recurrent): Post
    {
        return $recurrent ? Post::factory()->make($data)
            : Post::factory()->nonRecurrent()->make($data);
    }

    private function assertCorrectJobScheduleDate(
        Carbon $correct,
        Carbon $scheduled
    ): void {
        $this->assertTrue($scheduled->isSameDay($correct));
        $this->assertTrue($scheduled->isSameHour($correct));
        $this->assertTrue($scheduled->isSameMinute($correct));
    }

}
