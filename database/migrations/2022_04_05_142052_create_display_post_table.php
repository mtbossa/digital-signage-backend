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
    Schema::create('display_post', function (Blueprint $table) {
      $table->id();

      $table->foreignId('display_id')
        ->nullable()
        ->constrained('displays', 'id')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->foreignId('post_id')
        ->nullable()
        ->constrained('posts', 'id')
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
    Schema::dropIfExists('display_post');
  }
};
