<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class PersistentLogin
{
    public const COOKIE = 'absensi_ict';

    public static function put(User $user): void
    {
        $minutes = (int) config('session.lifetime', 5256000);

        Cookie::queue(cookie(
            self::COOKIE,
            self::pack((int) $user->getAuthIdentifier()),
            $minutes,
            config('session.path', '/'),
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        ));
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(
            self::COOKIE,
            config('session.path', '/'),
            config('session.domain')
        ));
    }

    public static function restore(Request $request): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user instanceof User) {
                self::put($user);
            }

            return;
        }

        $userId = self::unpack($request->cookie(self::COOKIE));
        if (!$userId) {
            return;
        }

        $user = User::query()->find($userId);
        if (!$user) {
            self::forget();

            return;
        }

        Auth::login($user, true);
        self::put($user);
    }

    private static function pack(int $userId): string
    {
        $signature = hash_hmac('sha256', (string) $userId, (string) config('app.key'));

        return $userId . '|' . $signature;
    }

    private static function unpack(mixed $value): ?int
    {
        if (!is_string($value) || !str_contains($value, '|')) {
            return null;
        }

        [$id, $signature] = explode('|', $value, 2);
        if (!ctype_digit($id) || $signature === '') {
            return null;
        }

        $expected = hash_hmac('sha256', $id, (string) config('app.key'));
        if (!hash_equals($expected, $signature)) {
            return null;
        }

        return (int) $id;
    }
}
