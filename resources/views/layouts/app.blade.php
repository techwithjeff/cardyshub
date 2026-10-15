<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Cardyshub') }}</title>
        <link rel="icon" href="{{ asset('brand/favicon-32.png') }}" sizes="32x32">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Same supplementary bundle the storefront layout loads. The account
             pages need it too: the wishlist grid lives here, and its hearts are
             handled by site.js. --}}
        <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
        <script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}" defer></script>
    </head>
    <body class="font-body antialiased">
        <div class="nav-progress" data-nav-progress aria-hidden="true"><span class="nav-progress-bar"></span></div>
        <span class="sr-only" role="status" aria-live="polite" data-nav-progress-status></span>

        <div class="min-h-screen bg-bone">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <div class="toast-stack" data-toast-stack role="status" aria-live="polite"></div>
    </body>
</html>
