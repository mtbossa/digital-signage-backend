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
                'required', 'string', 'max:255', 'unique:stores',
            ],
        ]);

        return Store::create(['name' => $request->name]);
    }

    public function show(Request $request, Store $store)
    {
        return $store;
    }

    public function update(Request $request, Store $store): Store
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255', 'unique:stores',
            ],
        ]);
        $store->update(['name' => $request->name]);

        return $store;
    }

    public function destroy(Store $store)
    {
        return $store->delete();
    }
}
