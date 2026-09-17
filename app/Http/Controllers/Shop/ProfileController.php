<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ShopProfileRequest;
use App\Repositories\ShopRepository;
use App\Services\AI\GeminiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * show profile.
     */
    public function index()
    {
        $shop = generaleSetting('shop');
        $shop?->load(['country', 'state', 'city']);

        return view('shop.profile.index', compact('shop'));
    }

    /**
     * edit profile
     */
    public function edit()
    {
        $shop = generaleSetting('shop');
        $shop?->load(['country', 'state', 'city']);

        return view('shop.profile.edit', compact('shop'));
    }

    /**
     * update profile
     */
    public function update(ShopProfileRequest $request)
    {
        /** @var \App\Models\Shop $shop */
        $shop = generaleSetting('shop');

        ShopRepository::updateByRequest($shop, $request);

        return to_route('shop.profile.index')->withSuccess(__('Profile updated successfully'));
    }

    /**
     * show change password form
     */
    public function changePassword()
    {
        return view('shop.profile.change-password');
    }

    /**
     * change password
     *
     * @model User $user
     */
    public function updatePassword(ChangePasswordRequest $request)
    {
        /** @var App\Models\User $user */
        $user = auth()->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withError(__('You have entered wrong password'));
        }
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->withSuccess(__('password change successfully'));
    }

    /**
     * Test Google Gemini API Key and Model.
     */
    public function testGeminiKey(Request $request, GeminiContentService $geminiService)
    {
        $request->validate([
            'api_key' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
        ]);

        $apiKey = $request->api_key;
        if (empty($apiKey)) {
            $shop = generaleSetting('shop');
            $apiKey = $geminiService->resolveApiKey($shop);
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => __('Please enter a Google Gemini API Key first.'),
            ], 422);
        }

        $model = $request->model ?? 'gemini-1.5-flash';
        $result = $geminiService->testConnection($apiKey, $model);

        return response()->json($result);
    }
}
