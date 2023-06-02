<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Manage\StoreSettingsRequest;
use App\Models\Setting;

class SettingsManagementController extends Controller
{
    public function update(StoreSettingsRequest $request)
    {
        $validated = $request->validated();

        Setting::updateOrCreate([
            'name' => $validated['name']
        ], [
            'value' => $validated['value']
        ]);

        return response()->json([
            'message' => 'Your settings has been saved.'
        ]);
    }
}
