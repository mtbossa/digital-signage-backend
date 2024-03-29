<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
  public function up()
  {
    Schema::create('invitations', function (Blueprint $table) {
      $table->id();
      $table->string('email', 255)->unique();
      $table->string('token', 32);
      $table->dateTime('registered_at')->nullable();
      $table->boolean('is_admin');

      $table->foreignIdFor(User::class, 'inviter'); // User how send the invitation email      
      $table->foreignId('store_id')
        ->nullable()
        ->constrained('stores', 'id')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();

      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('invitations');
  }
}
