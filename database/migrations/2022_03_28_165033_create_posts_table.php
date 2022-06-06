<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('posts', function (Blueprint $table) {
      $table->id();
      $table->string('description', 100);

      // nullable because could have a recurrence, so it will know when to show by recurrence
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();

      $table->time('start_time');
      $table->time('end_time');
      $table->mediumInteger('expose_time')->nullable();

      $table->foreignId('media_id')
        ->constrained('medias', 'id')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->foreignId('recurrence_id')
        ->nullable()
        ->constrained('recurrences', 'id')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('posts');
  }
};
