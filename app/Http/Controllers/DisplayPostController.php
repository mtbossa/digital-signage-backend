<?php

namespace App\Http\Controllers;

use App\Models\Display;
use Illuminate\Http\Request;

class DisplayPostController extends Controller
{
  public function index(Request $request, Display $display)
  {
    $display->load('posts');
    $display['posts'] = $display->posts->toArray();
    return $display;
  }
}
