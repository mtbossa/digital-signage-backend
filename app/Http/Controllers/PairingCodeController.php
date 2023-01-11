<?php

namespace App\Http\Controllers;

use App\Jobs\ExpirePairingCode;
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

            $generated = $generator->generate();
            $foundOrNot = PairingCode::query()->where('code', $generated['code'])->count();

            if ($foundOrNot === 0) {
                $pairing_code = PairingCode::create(['code' => $generated['code'], 'expires_at' => $generated['expires_at']]);

                ExpirePairingCode::dispatch($pairing_code)->delay($generated['expires_at']->setMicro(0));

                return $pairing_code;
            }
        } while ($tries <= 100);

        return response()->json(['error' => 'Service unavailable.'], Response::HTTP_SERVICE_UNAVAILABLE);
    }

    public function update(Request $request, string $pairing_code): JsonResponse
    {
        $pairing_code_model = PairingCode::query()->where('code', $pairing_code)->with('display')->firstOrFail();
        $display = $pairing_code_model->display;

        if (is_null($display)) {
            return response()->json(['error' => 'Display not created yet.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $new_token = $display->createToken('display_access_token')->plainTextToken;

        $pairing_code_model->delete();

        return response()->json(['api_token' => $new_token, 'display_id' => $display->id], Response::HTTP_OK);
    }
}
