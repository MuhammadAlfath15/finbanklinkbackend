<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat tabel admins
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Buat tabel bank_users
        Schema::create('bank_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Migrasikan data admin dari tabel users ke admins
        $admins = DB::table('users')->where('role', 'admin')->get();
        foreach ($admins as $admin) {
            DB::table('admins')->insert([
                'name' => $admin->name,
                'email' => $admin->email,
                'password' => $admin->password,
                'remember_token' => $admin->remember_token,
                'created_at' => $admin->created_at,
                'updated_at' => $admin->updated_at,
            ]);
        }

        // 4. Migrasikan data bank user dari tabel users ke bank_users
        $bankUsers = DB::table('users')->where('role', 'bank')->get();
        foreach ($bankUsers as $bankUser) {
            DB::table('bank_users')->insert([
                'name' => $bankUser->name,
                'email' => $bankUser->email,
                'password' => $bankUser->password,
                'bank_id' => $bankUser->bank_id,
                'remember_token' => $bankUser->remember_token,
                'created_at' => $bankUser->created_at,
                'updated_at' => $bankUser->updated_at,
            ]);
        }

        // 5. Hapus admin & bank dari tabel users agar hanya tersisa standard user
        DB::table('users')->whereIn('role', ['admin', 'bank'])->delete();

        // 6. Hapus foreign key bank_id dan drop kolom role & bank_id dari tabel users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'bank_id')) {
                // Drop foreign key first
                $table->dropForeign(['bank_id']);
                $table->dropColumn('bank_id');
            }
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Kembalikan kolom role & bank_id ke tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('user')->after('password');
            }
            if (!Schema::hasColumn('users', 'bank_id')) {
                $table->foreignId('bank_id')->nullable()->after('bio')->constrained('banks')->nullOnDelete();
            }
        });

        // 2. Kembalikan data dari admins ke users
        $admins = DB::table('admins')->get();
        foreach ($admins as $admin) {
            // Update jika email sudah ada, jika tidak insert baru
            DB::table('users')->updateOrInsert(
                ['email' => $admin->email],
                [
                    'name' => $admin->name,
                    'password' => $admin->password,
                    'role' => 'admin',
                    'created_at' => $admin->created_at,
                    'updated_at' => $admin->updated_at,
                ]
            );
        }

        // 3. Kembalikan data dari bank_users ke users
        $bankUsers = DB::table('bank_users')->get();
        foreach ($bankUsers as $bankUser) {
            DB::table('users')->updateOrInsert(
                ['email' => $bankUser->email],
                [
                    'name' => $bankUser->name,
                    'password' => $bankUser->password,
                    'role' => 'bank',
                    'bank_id' => $bankUser->bank_id,
                    'created_at' => $bankUser->created_at,
                    'updated_at' => $bankUser->updated_at,
                ]
            );
        }

        // 4. Drop tabel admins & bank_users
        Schema::dropIfExists('admins');
        Schema::dropIfExists('bank_users');
    }
};
