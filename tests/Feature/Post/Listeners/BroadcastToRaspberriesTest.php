<?php


namespace Post\Listeners;

use App\Events\ShouldStartPost;
use App\Models\Display;
use App\Models\Raspberry;
use App\Notifications\PostStarted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Post\Traits\PostTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class BroadcastToRaspberriesTest extends TestCase
{
    use RefreshDatabase, PostTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->media = $this->_createMedia();
        $this->post = $this->_createPost([
            'media_id' => $this->media->id,
        ]);
    }

    /**
     * @test
     */
    public function should_notify_only_raspberries_which_are_not_null()
    {
        Notification::fake();
        Bus::fake();
        Event::fakeExcept([ShouldStartPost::class]);

        $displaysWithRaspberry = Display::factory(3)->create();
        $displayWithoutRaspberry = Display::factory(2)->create();

        foreach (
            [...$displaysWithRaspberry, ...$displayWithoutRaspberry] as $display
        ) {
            $this->post->displays()->attach($display->id);
        }

        foreach ($displayWithoutRaspberry as $display) {
            Raspberry::factory()->create();
        }

        foreach ($displaysWithRaspberry as $display) {
            $raspberry = Raspberry::factory()->make()->toArray();
            $display->raspberry()->create($raspberry);
        }

        event(new ShouldStartPost($this->post));

        Notification::assertTimesSent(count($displaysWithRaspberry),
            PostStarted::class);
        Notification::assertSentTo(
            Raspberry::query()->whereNotNull('display_id')->get(),
            PostStarted::class
        );
        Notification::assertNotSentTo(
            Raspberry::query()->whereNull('display_id')->get(),
            PostStarted::class
        );
    }
}
