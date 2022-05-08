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
    Schema::create('medias', function (Blueprint $table) {
      $table->id();
      $table->string('description', 50);
      $table->string('type', 10);
      $table->string('filename', 255)->unique();
      $table->string('extension', 5);
      $table->string('path', 255);
      $table->integer('size_kb');
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
    Schema::dropIfExists('medias');
  }
};
