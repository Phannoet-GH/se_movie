<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - CinePulse Movies' : 'CinePulse - Discover & Stream Movies' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #08080a;
            color: #f4f4f5;
        }
    </style>
</head>
<body class="bg-[#08080a] text-zinc-100 min-h-screen flex flex-col antialiased selection:bg-red-600 selection:text-white"
      x-data="{
          toast: { show: false, message: '', type: 'success' },
          showToast(msg, type = 'success') {
              this.toast.message = msg;
              this.toast.type = type;
              this.toast.show = true;
              setTimeout(() => { this.toast.show = false; }, 3500);
          }
      }"
      @toast-message.window="showToast($event.detail.message, $event.detail.type)"
>
    <!-- Navigation Bar -->
    <x-navbar />

    <!-- Main Content Slot -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Global Trailer Modal -->
    <x-trailer-modal />

    <!-- Footer -->
    <x-footer />

    <!-- Global Toast Notification -->
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-2xl backdrop-blur-md border"
         :class="{
             'bg-zinc-900/95 border-red-500/40 text-red-400': toast.type === 'error',
             'bg-zinc-900/95 border-emerald-500/40 text-emerald-400': toast.type === 'success',
             'bg-zinc-900/95 border-cyan-500/40 text-cyan-400': toast.type === 'info'
         }"
         style="display: none;"
    >
        <span class="text-xl">
            <template x-if="toast.type === 'success'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </template>
            <template x-if="toast.type === 'error'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </template>
            <template x-if="toast.type === 'info'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </template>
        </span>
        <p class="text-sm font-medium text-zinc-100" x-text="toast.message"></p>
    </div>

    <!-- Server Flash Messages -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('toast-message', {
                    detail: { message: @js(session('success')), type: 'success' }
                }));
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('toast-message', {
                    detail: { message: @js(session('error')), type: 'error' }
                }));
            });
        </script>
    @endif
    @if (session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('toast-message', {
                    detail: { message: @js(session('info')), type: 'info' }
                }));
            });
        </script>
    @endif
</body>
</html>
