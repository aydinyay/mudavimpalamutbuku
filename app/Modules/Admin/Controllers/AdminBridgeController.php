<?php
declare(strict_types=1);
namespace App\Modules\Admin\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminBridgeController
{
    public function redirectToRestoran()
    {
        DB::statement("CREATE TABLE IF NOT EXISTS auto_login_tokens (
            token      VARCHAR(64)  NOT NULL,
            user_id    INT          NOT NULL,
            username   VARCHAR(100) NOT NULL,
            name       VARCHAR(100) NOT NULL,
            role       VARCHAR(50)  NOT NULL,
            pin_hash   VARCHAR(255) NULL,
            expires_at TIMESTAMP    NOT NULL,
            used_at    TIMESTAMP    NULL,
            PRIMARY KEY (token),
            INDEX idx_expires (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        DB::statement("DELETE FROM auto_login_tokens WHERE expires_at < NOW()");

        $laravelUser = auth()->user();

        $restoranUser = DB::selectOne(
            "SELECT u.id, u.name, u.username, r.code AS role, u.pin_hash
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? AND u.deleted_at IS NULL
             LIMIT 1",
            [$laravelUser->email]
        );

        if (!$restoranUser) {
            $restoranUser = DB::selectOne(
                "SELECT u.id, u.name, u.username, r.code AS role, u.pin_hash
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE u.id = 1 AND u.deleted_at IS NULL
                 LIMIT 1"
            );
        }

        if (!$restoranUser) {
            return redirect()->route('admin.dashboard')->with('error', 'Restoran kullanıcısı bulunamadı.');
        }

        $token = Str::random(64);

        DB::table('auto_login_tokens')->insert([
            'token'      => $token,
            'user_id'    => $restoranUser->id,
            'username'   => $restoranUser->username,
            'name'       => $restoranUser->name,
            'role'       => $restoranUser->role,
            'pin_hash'   => $restoranUser->pin_hash,
            'expires_at' => now()->addMinutes(5),
            'used_at'    => null,
        ]);

        return redirect('/mudavim/autologin.php?token=' . $token);
    }
}
