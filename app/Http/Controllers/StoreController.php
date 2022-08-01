<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{

    public function index()
    {
        return Store::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255', 'unique:stores'
            ]
        ]);
        $store = Store::create($request->all());
        $new_token = $store->createToken('store_access_token');
        $store->token = $new_token;
        return $store;
    }

    public function show(Request $request, Store $store)
    {
        return $store;
    }

    public function update(Request $request, Store $store): Store
    {
        $store->update($request->all());

        return $store;
    }

    public function destroy(Store $store)
    {
        return $store->delete();
    }
}
