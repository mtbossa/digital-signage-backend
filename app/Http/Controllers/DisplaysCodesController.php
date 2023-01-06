<?php

namespace App\Http\Controllers;

use App\Models\DisplaysCodes;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisplaysCodesController extends Controller
{
  /**
   * @throws Exception
   */
  public function store(Request $request)
  {
      $code = random_int(100000, 999999);
      return DisplaysCodes::create(['code' => $code]);
    }
}
