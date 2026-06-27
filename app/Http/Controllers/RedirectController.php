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
            'ip' => $request->ip(),
            'referer' => $request->headers->get('referer'),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->away($link->original_url, 302);
    }
}
