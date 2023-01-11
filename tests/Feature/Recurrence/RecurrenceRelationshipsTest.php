<?php

namespace Tests\Feature\Recurrence;

use App\Models\Display;
use App\Models\Media;
use App\Models\Post;
use App\Models\Recurrence;
use App\Notifications\DisplayPost\PostDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\Recurrence\Traits\RecurrenceTestsTrait;
use Tests\Feature\Traits\AuthUserTrait;
use Tests\TestCase;

class RecurrenceRelationshipsTest extends TestCase
{
    use RefreshDatabase, RecurrenceTestsTrait, AuthUserTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->_authUser();
    }

    /** @test */
    public function a_recurrence_may_have_many_posts()
    {
        $post_amount = 3;
        $recurrence = Recurrence::factory()->create();
        $media = Media::factory()->create();
        Post::factory()->create(['media_id' => $media->id]);
        $posts = Post::factory($post_amount)->create(['recurrence_id' => $recurrence->id, 'media_id' => $media->id]);

        $this->assertInstanceOf(Post::class, $recurrence->posts[0]);
        $this->assertEquals($post_amount, $recurrence->posts->count());
        $this->assertDatabaseHas('posts', ['id' => $posts[0]->id, 'recurrence_id' => $recurrence->id]);
    }

    /** @test */
    public function when_recurrence_is_deleted_should_delete_all_posts_that_belongs_to_it()
    {
        $media = Media::factory()->create(0);

        $deletedRecurrence = Recurrence::factory()->create();
        $recurrence = Recurrence::factory()->create();

        $shouldDeletePosts = Post::factory(2)->create([
            'media_id' => $media->id, 'recurrence_id' => $deletedRecurrence->id,
        ]);
        $posts = Post::factory(2)->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);

        $this->deleteJson(route('recurrences.destroy', $deletedRecurrence->id))->assertOk();

        foreach ($shouldDeletePosts as $shouldDeletePost) {
            $this->assertModelMissing($shouldDeletePost);
        }

        foreach ($posts as $post) {
            $this->assertModelExists($post);
        }
    }

    /** @test */
    public function when_recurrence_is_deleted_should_delete_all_posts_that_belongs_to_it_and_should_notify_all_displays_with_post_deleted(
  ) {
        Notification::fake();

        $media = Media::factory()->create(0);

        $deletedRecurrence = Recurrence::factory()->create();
        $recurrence = Recurrence::factory()->create();

        $notifiableDisplays = Display::factory(2)->create();
        $displays = Display::factory(2)->create();

        $shouldDeletePosts = [];
        foreach ($notifiableDisplays as $notifiableDisplay) {
            $post = Post::factory()->create(['media_id' => $media->id, 'recurrence_id' => $deletedRecurrence->id]);
            $post->displays()->attach($notifiableDisplay->id);
            $shouldDeletePosts[] = $post;
        }

        $posts = [];
        foreach ($displays as $display) {
            $post = Post::factory()->create(['media_id' => $media->id, 'recurrence_id' => $recurrence->id]);
            $post->displays()->attach($display->id);
            $posts[] = $post;
        }

        $this->deleteJson(route('recurrences.destroy', $deletedRecurrence->id))->assertOk();

        foreach ($notifiableDisplays as $notifiableDisplay) {
            Notification::assertSentTo($notifiableDisplay, PostDeleted::class);
        }

        foreach ($displays as $display) {
            Notification::assertNotSentTo($display, PostDeleted::class);
        }
    }
}
