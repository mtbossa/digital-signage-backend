<?php

namespace App\Actions\Display;

use App\Http\Requests\Display\StoreDisplayRequest;
use App\Models\Display;
use App\Models\PairingCode;
use App\Models\Raspberry;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StoreDisplayAction
{
    public function handle(StoreDisplayRequest $request): Display|JsonResponse
    {
        // TODO move to new request custom rule
        $pairing_code = PairingCode::query()->where('code', $request->pairing_code)->first();
        if (is_null($pairing_code)) {
            return response()->json(['error' => 'Pairing code not found.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $display = Display::create($request->except(['raspberry_id']));
        $display->pairing_code()->associate($pairing_code);

        if ($request->raspberry_id) {
            $raspberry = Raspberry::findOrFail($request->raspberry_id);
            $display->raspberry()->save($raspberry);
        }

        if ($request->store_id) {
            $store = Store::findOrFail($request->store_id);
            $display->store()->associate($store);
        }

        $display->save();

        return $display;
    }
}
