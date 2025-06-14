<?php

namespace App\Http\Controllers\Settings;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        $tokens = $request->user()->tokens()->get();
        $newToken = $request->session()->get('new_token');

        return Inertia::render('settings/ApiToken', [
            'tokens' => $tokens,
            'newToken' => $newToken,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // createToken() returns a NewAccessToken object:
        $plainText = $request->user()
            ->createToken($request->name)
            ->plainTextToken;

        // flash it so we can display it once
        return redirect()
            ->route('api-tokens.index')
            ->with('new_token', $plainText);
    }

    public function destroy(Request $request, PersonalAccessToken $token)
    {
        // ensure the token belongs to this user
        if ($token->tokenable_id !== $request->user()->id) {
            abort(403);
        }

        $token->delete();

        return redirect()->route('api-tokens.index');
    }
}
