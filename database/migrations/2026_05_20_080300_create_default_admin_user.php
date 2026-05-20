<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $email = env('DEFAULT_ADMIN_EMAIL', 'admin@finbanklink.local');
        $name = env('DEFAULT_ADMIN_NAME', 'Admin FinBankLink');
        $password = env('DEFAULT_ADMIN_PASSWORD', 'Admin@123456');

        $existing = DB::table('users')->where('email', $email)->first();
        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update([
                'role' => 'admin',
                'updated_at' => now(),
            ]);
            return;
        }

        DB::table('users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $email = env('DEFAULT_ADMIN_EMAIL', 'admin@finbanklink.local');
        DB::table('users')->where('email', $email)->delete();
    }
};
