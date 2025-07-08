<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ env('APP_THEME', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Flatpickr  --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    {{-- It will not apply locale yet  --}}
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

    {{-- You need to set here the default locale or any global flatpickr settings--}}
    <script>
        flatpickr.localize(flatpickr.l10ns.id);
    </script>
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{ $slot }}

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
