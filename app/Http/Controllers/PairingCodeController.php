<?php

namespace App\Http\Controllers;

use App\Jobs\ExpirePairingCode;
use App\Models\PairingCode;
use App\Services\PairingCodeGeneratorService;
use Carbon\Carbon;
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
            
            $generated = $generator->generate();
            $foundOrNot = PairingCode::query()->where('code', $generated['code'])->count();

            if ($foundOrNot === 0) {
                $expires_at = $generated['expires_at']->format('Y-m-d H:i:s');
                $pairing_code = PairingCode::create(['code' => $generated['code'], 'expires_at' => $expires_at]);
                ExpirePairingCode::dispatch($pairing_code)->delay($expires_at);
                return $pairing_code;
            }
        } while ($tries <= 100);

        return response()->json(['error' => 'Service unavailable.'], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
