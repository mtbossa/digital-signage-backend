<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
  use HasFactory;

  protected $fillable = ['email', 'token', 'inviter', 'is_admin', 'store_id'];

  public static function generateInvitationToken(string $email): string
  {
    return substr(md5(rand(0, 9).$email.time()), 0, 32);
  }
}
