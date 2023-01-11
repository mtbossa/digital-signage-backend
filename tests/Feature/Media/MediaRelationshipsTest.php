<?php

namespace Tests\Feature\Media;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Media\Traits\MediaTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class MediaRelationshipsTest extends TestCase
{
    use RefreshDatabase, MediaTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
    }

    /** @test */
    public function a_media_may_have_many_posts()
    {
        $display_amount = 3;
        $media = Media::factory()->create();
        $posts = Post::factory($display_amount)->create(['media_id' => $media->id]);

        $this->assertEquals($display_amount, $media->posts->count());
        $this->assertInstanceOf(Post::class, $media->posts[0]);
        $this->assertDatabaseHas('posts', ['id' => $posts[0]->id, 'media_id' => $media->id]);
    }

    /** @test */
    public function when_media_is_deleted_should_delete_all_posts_that_belongs_to_it()
    {
        $deletedMedia = Media::factory()->create();
        $media = Media::factory()->create();

        $shouldDeletePosts = Post::factory(2)->create(['media_id' => $deletedMedia->id]);
        $posts = Post::factory(2)->create(['media_id' => $media->id]);

        $this->deleteJson(route('medias.destroy', $deletedMedia->id))->assertOk();

        foreach ($shouldDeletePosts as $shouldDeletePost) {
            $this->assertModelMissing($shouldDeletePost);
        }

        foreach ($posts as $post) {
            $this->assertModelExists($post);
        }
    }

    /** @test */
    public function when_media_is_deleted_should_delete_all_posts_that_belongs_to_it_and_should_notify_all_displays_with_post_deleted(
  ) {
        Notification::fake();

        $deletedMedia = Media::factory()->create();
        $media = Media::factory()->create();

        $notifiableDisplays = Display::factory(2)->create();
        $displays = Display::factory(2)->create();

        $shouldDeletePosts = [];
        foreach ($notifiableDisplays as $notifiableDisplay) {
            $post = Post::factory()->create(['media_id' => $deletedMedia->id]);
            $post->displays()->attach($notifiableDisplay->id);
            $shouldDeletePosts[] = $post;
        }

        $posts = [];
        foreach ($displays as $display) {
            $post = Post::factory()->create(['media_id' => $media->id]);
            $post->displays()->attach($display->id);
            $posts[] = $post;
        }

        $this->deleteJson(route('medias.destroy', $deletedMedia->id))->assertOk();

        foreach ($notifiableDisplays as $notifiableDisplay) {
            Notification::assertSentTo($notifiableDisplay, PostDeleted::class);
        }

        foreach ($displays as $display) {
            Notification::assertNotSentTo($display, PostDeleted::class);
        }
    }
}
