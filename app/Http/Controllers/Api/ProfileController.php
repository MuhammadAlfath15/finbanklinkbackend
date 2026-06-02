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
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'phone'  => $user->phone,
            'bio'    => $user->bio,
            'avatar' => $user->avatar,
            'role'   => $user->role,
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
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|string|email|unique:users,email,' . $user->id,
            'phone'  => 'sometimes|nullable|string|max:20',
            'bio'    => 'sometimes|nullable|string|max:500',
            'avatar' => 'sometimes|nullable|string',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'bio']);

        if ($request->has('avatar') && $request->avatar) {
            $avatarStr = $request->avatar;
            if (preg_match('/^data:image\/(\w+);base64,/', $avatarStr, $type)) {
                $base64Data = substr($avatarStr, strpos($avatarStr, ',') + 1);
                $ext = strtolower($type[1]);

                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $decoded  = base64_decode($base64Data);
                    $fileName = 'user_' . $user->id . '_' . time() . '.' . $ext;
                    $dir      = public_path('avatars');

                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    file_put_contents($dir . '/' . $fileName, $decoded);
                    $data['avatar'] = url('avatars/' . $fileName);
                }
            } else {
                $data['avatar'] = $avatarStr;
            }
        }

        $user->fill($data);
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user'    => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'bio'    => $user->bio,
                'avatar' => $user->avatar,
                'role'   => $user->role,
            ],
        ]);
    }

    /**
     * POST /api/profile/change-password
     * Ganti password user yang sedang login (verifikasi password lama dulu)
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        if (!\Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini tidak sesuai.'], 422);
        }

        $user->password = \Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password berhasil diubah.']);
    }

    /**
     * DELETE /api/account
     * Hapus akun user beserta semua token (konfirmasi dengan password)
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!\Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password tidak sesuai. Akun tidak dihapus.'], 422);
        }

        // Cabut semua token Sanctum sebelum hapus user
        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }
}
