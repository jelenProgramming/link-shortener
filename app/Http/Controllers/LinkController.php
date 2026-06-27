<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LinkController extends Controller
{
    public function index()
    {
        $links = Link::query()
            ->withCount('clicks')
            ->latest()
            ->get()
            ->map(fn (Link $link) => $this->present($link));

        return response()->json($links);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'slug' => ['nullable', 'alpha_dash', 'min:3', 'max:40', 'unique:links,slug'],
        ]);

        $link = Link::create([
            'slug' => $data['slug'] ?? $this->uniqueSlug(),
            'original_url' => $data['url'],
        ]);

        $link->loadCount('clicks');

        return response()->json($this->present($link), 201);
    }

    public function show(string $slug)
    {
        $link = Link::where('slug', $slug)->withCount('clicks')->firstOrFail();

        $byDay = $link->clicks()
            ->select(DB::raw('date(created_at) as day'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $topReferers = $link->clicks()
            ->select('referer', DB::raw('count(*) as count'))
            ->groupBy('referer')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'referer' => $row->referer ?: 'Direct / unknown',
                'count' => (int) $row->count,
            ]);

        $recent = $link->clicks()
            ->latest()
            ->limit(10)
            ->get(['referer', 'created_at'])
            ->map(fn ($c) => [
                'referer' => $c->referer ?: 'Direct / unknown',
                'at' => $c->created_at->toIso8601String(),
            ]);

        return response()->json([
            'link' => $this->present($link),
            'stats' => [
                'total' => $link->clicks_count,
                'by_day' => $byDay,
                'top_referers' => $topReferers,
                'recent' => $recent,
            ],
        ]);
    }

    public function destroy(string $slug)
    {
        Link::where('slug', $slug)->firstOrFail()->delete();

        return response()->json(['deleted' => true]);
    }

    private function present(Link $link): array
    {
        return [
            'slug' => $link->slug,
            'original_url' => $link->original_url,
            'short_url' => url($link->slug),
            'clicks' => $link->clicks_count ?? 0,
            'created_at' => $link->created_at->toIso8601String(),
        ];
    }

    private function uniqueSlug(): string
    {
        do {
            $slug = Str::lower(Str::random(6));
        } while (Link::where('slug', $slug)->exists());

        return $slug;
    }
}
