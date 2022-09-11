<?php

namespace App\Actions\Display;

use App\Http\Requests\Display\StoreDisplayRequest;
use App\Mail\InstallationLink;
use App\Models\Display;
use App\Models\Raspberry;
use App\Models\Store;
use Illuminate\Support\Facades\Mail;

class StoreDisplayAction
{
    public function handle(StoreDisplayRequest $request): Display
    {
        $display = Display::create($request->except(['raspberry_id']));

        if ($request->raspberry_id) {
            $raspberry = Raspberry::findOrFail($request->raspberry_id);
          $display->raspberry()->save($raspberry);
        }

      if ($request->store_id) {
        $store = Store::findOrFail($request->store_id);
        $display->store()->associate($store);
      }

      $new_token = $display->createToken('display_access_token');
      $display->token = $new_token;

      Mail::to($request->user())->queue(new InstallationLink($display));

      return $display;
    }
}
