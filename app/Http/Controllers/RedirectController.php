<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request, string $slug)
    {
        $link = Link::where('slug', $slug)->first();

        if (! $link) {
            return response()->json(['error' => 'Short link not found'], 404);
        }

        $link->clicks()->create([
            'ip' => $this->truncateIp($request->ip()),
            'referer' => $request->headers->get('referer'),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->away($link->original_url, 302);
    }

    /**
     * Zero out the host portion of the IP so we keep enough for coarse
     * geo/analytics without storing a precise, identifying address.
     */
    private function truncateIp(?string $ip): ?string
    {
        if (! $ip) {
            return null;
        }

        if (str_contains($ip, '.')) {
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts);
        }

        if (str_contains($ip, ':')) {
            $parts = explode(':', $ip);

            return implode(':', array_pad(array_slice($parts, 0, 4), count($parts), '0'));
        }

        return $ip;
    }
}
