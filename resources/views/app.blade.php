<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'light') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Light mode is the default; dark is applied only when explicitly chosen. --}}
        <script nonce="{{ $cspNonce ?? '' }}">
            (function() {
                var stored = null;

                try {
                    stored = window.localStorage.getItem('appearance');
                } catch (error) {
                    stored = null;
                }

                if (stored === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style nonce="{{ $cspNonce ?? '' }}">
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        @php($favicon = \App\Models\SiteSetting::get('favicon_path'))
        @if ($favicon)
            <link rel="icon" href="{{ $favicon }}">
            <link rel="apple-touch-icon" href="{{ $favicon }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js', "resources/js/pages/{$page['component']}.vue"])

        @php($seo = $page['props']['seo'] ?? null)

        @if ($seo)
            {{-- SEO metadata is rendered on the server so crawlers see it in the initial HTML. --}}
            <title>{{ $seo['title'] }}</title>
            @if (! empty($seo['description']))
                <meta name="description" content="{{ $seo['description'] }}">
            @endif
            <meta name="robots" content="{{ $seo['robots'] }}">
            <link rel="canonical" href="{{ $seo['canonical'] }}">

            <meta property="og:type" content="{{ $seo['og']['type'] }}">
            <meta property="og:title" content="{{ $seo['og']['title'] }}">
            @if (! empty($seo['og']['description']))
                <meta property="og:description" content="{{ $seo['og']['description'] }}">
            @endif
            <meta property="og:url" content="{{ $seo['og']['url'] }}">
            <meta property="og:site_name" content="{{ $seo['og']['site_name'] }}">
            @if (! empty($seo['og']['image']))
                <meta property="og:image" content="{{ $seo['og']['image'] }}">
            @endif

            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:title" content="{{ $seo['og']['title'] }}">
            @if (! empty($seo['og']['description']))
                <meta name="twitter:description" content="{{ $seo['og']['description'] }}">
            @endif
            @if (! empty($seo['og']['image']))
                <meta name="twitter:image" content="{{ $seo['og']['image'] }}">
            @endif
            @if (! empty($seo['twitterHandle']))
                <meta name="twitter:site" content="{{ $seo['twitterHandle'] }}">
            @endif

            @foreach (($seo['schemas'] ?? []) as $schema)
                <script type="application/ld+json" nonce="{{ $cspNonce ?? '' }}">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
            @endforeach
        @else
            <x-inertia::head>
                <title>{{ config('app.name', 'Solve CMS') }}</title>
            </x-inertia::head>
        @endif
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
