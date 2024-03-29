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
    Schema::create('displays', function (Blueprint $table) {
      $table->id();
      
      $table->string('name', 100);
      $table->unsignedDecimal('size');
      $table->unsignedInteger('width');
      $table->unsignedInteger('height');
      $table->boolean('touch')->default(false);
      $table->text('observation')->nullable();

      $table->foreignId('store_id')
        ->nullable()
        ->constrained()
        ->cascadeOnUpdate()
        ->nullOnDelete();
      
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
    Schema::dropIfExists('displays');
  }
};
