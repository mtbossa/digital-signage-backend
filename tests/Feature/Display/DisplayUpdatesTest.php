<?php

namespace Tests\Feature\Display;

use App\Http\Resources\RaspberryPostsResource;
use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Services\DisplayUpdatesCache\DisplayUpdatesCacheKeysEnum;
use App\Services\DisplayUpdatesCache\DisplayUpdatesCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Display\Traits\DisplayTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class DisplayUpdatesTest extends TestCase
{
    use RefreshDatabase, DisplayTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
        $this->media = Media::factory()->create();
        $this->display = $this->_createDisplay();
    }

    /** @test */
    public function should_return_empty_array_when_there_arent_updates()
    {
        $response = $this->getJson(route('displays.updates', $this->display->id));
        $response->assertOk()->assertJson([]);
    }

    /** @test */
    public function when_posts_are_created_should_return_the_created_posts_for_this_display()
    {
        $cacheService = new DisplayUpdatesCacheService();

        $randomDisplay = Display::factory()->create();
        Post::factory()->create(['media_id' => $this->media->id])->displays()->attach($randomDisplay);

        $posts = Post::factory(2)->create(['media_id' => $this->media->id]);
        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);

            $cacheService->setCurrentCache(DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated, $this->display->id, $post->id);
        }

        $response = $this->getJson(route('displays.updates', $this->display->id));
        $response->assertOk()->assertExactJson(['PostCreated' => RaspberryPostsResource::collection($posts)->resolve()]);
    }

    /** @test */
    public function should_return_only_the_create_posts_that_have_not_already_been_returned()
    {
        $cacheService = new DisplayUpdatesCacheService();
        // Random to be sure
        $randomDisplay = Display::factory()->create();
        Post::factory()->create(['media_id' => $this->media->id])->displays()->attach($randomDisplay);

        // First Post
        $post1 = Post::factory()->create(['media_id' => $this->media->id]);
        $post1->displays()->attach($this->display->id);
        $cacheService->setCurrentCache(DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated, $this->display->id, $post1->id);

        // Client requests the updates and we return the created one
        $response = $this->getJson(route('displays.updates', $this->display->id));
        $response->assertOk()->assertExactJson(['PostCreated' => RaspberryPostsResource::collection([$post1])->resolve()]);

        // Second and third posts are create after the first one and after requesting for updated
        $posts = Post::factory(2)->create(['media_id' => $this->media->id]);
        foreach ($posts as $post) {
            $post->displays()->attach($this->display->id);

            $cacheService->setCurrentCache(DisplayUpdatesCacheKeysEnum::DisplayUpdatesPostCreated, $this->display->id, $post->id);
        }

        // Must have only the two new ones, not the first one
        $response = $this->getJson(route('displays.updates', $this->display->id));
        $response->assertOk()->assertExactJson(['PostCreated' => RaspberryPostsResource::collection($posts)->resolve()]);
    }
}
