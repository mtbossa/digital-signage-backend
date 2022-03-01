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
    Schema::create('raspberries', function (Blueprint $table) {
      $table->id();
      $table->string('short_name', 30);
      $table->macAddress('mac_address');
      $table->string('serial_number', 50);
      $table->dateTime('last_boot')->nullable();
      $table->text('observation')->nullable();
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
    Schema::dropIfExists('raspberries');
  }
};
