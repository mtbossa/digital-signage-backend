<?php

namespace App\Actions\Display;

use App\Http\Requests\Display\UpdateDisplayRequest;
use App\Models\Display;
use App\Models\Raspberry;

class UpdateDisplayAction
{
    public function handle(UpdateDisplayRequest $request, Display $display)
    {
        $display->update($request->safe()->except(['raspberry_id']));

        if ($request->raspberry_id) {
            $current_raspberry = $display->raspberry;

            if ($current_raspberry->id !== $request->raspberry_id) {
                $raspberry = Raspberry::findOrFail($request->raspberry_id);

                $current_raspberry->display_id = null;
                $current_raspberry->save();

                $display->raspberry()->save($raspberry);
            }
        } else {
            if ($display->raspberry) {
                $current_raspberry = $display->raspberry;
                $current_raspberry->display_id = null;
                $current_raspberry->save();
            }
        }

        return $display;
    }
}
