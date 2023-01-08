<?php

namespace App\Http\Controllers;

use App\Models\PairingCode;
use Exception;
use Illuminate\Http\Request;

class PairingCodeController extends Controller
{
  /**
   * @throws Exception
   */
  public function store(Request $request)
  {
      $code = random_int(100000, 999999);
      return PairingCode::create(['code' => $code]);
    }
}
