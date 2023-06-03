<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\AuthenticateRequest;
use App\Http\Requests\Api\Auth\StoreRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'done_setup' => false,
            'user_type' => $validated['user_type']
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Successfully created.',
            'user' => $this->user()['user'],
            'permissions' => $this->user()['permissions']
        ]);
    }

    public function login(AuthenticateRequest $request)
    {
        $validated = $request->validated();
        if (Auth::attempt($validated)) {
            $request->session()->regenerate();

            return response()->json([
                'message' => 'Successfully authenticated.',
                'user' => $this->user()['user'],
                'permissions' => $this->user()['permissions']
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials.'
        ], 422);
    }

    public function user()
    {
        return [
            'user' => auth()->user(),
            'permissions' => auth()->user()->getPermissionsViaRoles()->pluck('name')->toArray()
        ];
    }

    public function authCheck(Request $request)
    {
        if (auth()->user()->relogin_required) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            auth()->user()->update([
                'relogin_required' => false
            ]);
            abort(401);
        }

        return response()->json([
            'data' => csrf_token(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}
