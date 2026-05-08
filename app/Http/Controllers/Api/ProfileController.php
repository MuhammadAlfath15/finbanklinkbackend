<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/profile
     * Ambil data profil user yang sedang login
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'bio'   => $user->bio,
            'role'  => $user->role,
        ]);
    }

    /**
     * PUT /api/profile
     * Update data profil user yang sedang login
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'bio'   => 'sometimes|nullable|string|max:500',
        ]);

        $user->fill($request->only(['name', 'email', 'phone', 'bio']));
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio'   => $user->bio,
                'role'  => $user->role,
            ],
        ]);
    }
}
