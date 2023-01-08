<?php

namespace App\Http\Controllers;

use App\Models\PairingCode;
use App\Services\PairingCodeGeneratorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PairingCodeController extends Controller
{
    /**
     * @throws Exception
     */
    public function store(Request $request, PairingCodeGeneratorService $generator): JsonResponse|PairingCode
    {
        $tries = 0;
        
        do {
            $tries++;
            
            $generated_code = $generator->generate();
            $foundOrNot = PairingCode::query()->where('code', $generated_code)->count();

            if ($foundOrNot === 0) {
                return PairingCode::create(['code' => $generated_code]);
            }
        } while ($tries <= 100);

        return response()->json(['error' => 'Service unavailable.'], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}