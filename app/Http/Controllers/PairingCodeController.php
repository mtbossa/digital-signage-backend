<?php

namespace App\Http\Controllers;

use App\Models\PairingCode;
use App\Services\PairingCodeGeneratorService;
use Exception;
use Illuminate\Http\Request;

class PairingCodeController extends Controller
{
    /**
     * @throws Exception
     */
    public function store(Request $request, PairingCodeGeneratorService $generator): PairingCode
    {
        $code = null;
        do {
            $generated_code = $generator->generate();
            $foundOrNot = PairingCode::query()->where('code', $generated_code)->count();

            if ($foundOrNot === 0) {
                $code = $generated_code;
            }
        } while (!$code);

        return PairingCode::create(['code' => $code]);
    }
}
