<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;

class TokenCookieService
{
    /**
     * Create an access token cookie.
     *
     * @param string $token
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function makeAccessCookie(string $token, string $name = 'token'): HttpCookie
    {
        $ttl = config('jwt.ttl', 60); // Default 60 minutes
        return $this->makeCookie($name, $token, $ttl);
    }

    /**
     * Create a refresh token cookie.
     *
     * @param string $token
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function makeRefreshCookie(string $token, string $name = 'refresh_token'): HttpCookie
    {
        $ttl = config('jwt.refresh_ttl', 20160); // Default 2 weeks
        return $this->makeCookie($name, $token, $ttl);
    }

    /**
     * Create a generic cookie with consistent security settings.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    protected function makeCookie(string $name, string $value, int $minutes): HttpCookie
    {
        $secure = config('session.secure');

        if ($secure === null) {
            $secure = config('app.env') !== 'local';
        }

        $sameSite = config('session.same_site', 'Lax');

        return cookie(
            $name,
            $value,
            $minutes,
            '/',
            null,
            $secure,
            true, // HttpOnly
            false,
            $sameSite
        );
    }

    /**
     * Create a cookie to forget the access token.
     *
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function forgetAccessCookie(string $name = 'token'): HttpCookie
    {
        return Cookie::forget($name);
    }

    /**
     * Create a cookie to forget the refresh token.
     *
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public function forgetRefreshCookie(string $name = 'refresh_token'): HttpCookie
    {
        return Cookie::forget($name);
    }
}
