<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreDisplaysController extends Controller
{
    /**
     * Gets the current Raspberry Posts through its Display
     */
    public function index(
        Request $request,
        Store $store
    ) {
        return $store->displays;
    }
}
