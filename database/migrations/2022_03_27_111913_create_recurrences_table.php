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
    Schema::create('recurrences', function (Blueprint $table) {
      $table->id();
      $table->string('description', 50);
      $table->tinyInteger('isoweekday')->nullable();
      $table->tinyInteger('day')->nullable();
      $table->tinyInteger('month')->nullable();
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
    Schema::dropIfExists('recurrences');
  }
};
