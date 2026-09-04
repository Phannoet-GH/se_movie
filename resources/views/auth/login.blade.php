@extends('layouts.app')

@section('content')
<div class="relative min-h-[80vh] flex items-center justify-center px-4 py-16 -mt-20 pt-32 overflow-hidden">
    <!-- Cinematic Background Vignette -->
    <div class="absolute inset-0 z-0">
        <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=1920&auto=format&fit=crop&q=80"
             alt="Background"
             class="w-full h-full object-cover opacity-20 filter blur-sm">
        <div class="absolute inset-0 bg-gradient-to-t from-[#08080a] via-[#08080a]/90 to-[#08080a]/70"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md p-8 sm:p-10 rounded-3xl bg-zinc-900/90 border border-zinc-800/90 shadow-2xl backdrop-blur-xl">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-red-600 to-rose-500 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-red-600/30">
                <svg class="w-6 h-6 text-white fill-current" viewBox="0 0 24 24">
                    <path d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75zM9.75 8.5a.75.75 0 00-1.15-.64l-4.5 3a.75.75 0 000 1.28l4.5 3A.75.75 0 009.75 14.5V8.5zm4.5 7a.75.75 0 001.15.64l4.5-3a.75.75 0 000-1.28l-4.5-3a.75.75 0 00-1.15.64v6z" />
                </svg>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Welcome Back</h2>
            <p class="text-xs text-zinc-400 mt-1">Sign in to manage your watchlist and sync your favorites</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-950/50 border border-red-900/60 text-red-300 text-xs">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">Email Address</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       placeholder="you@example.com"
                       class="w-full bg-zinc-950/90 border border-zinc-800 focus:border-red-500 rounded-xl px-4 py-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all">
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-xs font-bold text-zinc-300 uppercase tracking-wider mb-2">Password</label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       placeholder="••••••••"
                       class="w-full bg-zinc-950/90 border border-zinc-800 focus:border-red-500 rounded-xl px-4 py-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between text-xs py-1">
                <label class="flex items-center gap-2 text-zinc-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-zinc-950 border-zinc-800 text-red-600 focus:ring-0 focus:ring-offset-0">
                    <span>Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-sm shadow-xl shadow-red-600/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                Sign In to CinePulse
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-zinc-800/80 text-center text-xs text-zinc-400">
            Don't have an account yet?
            <a href="{{ route('register') }}" class="font-bold text-red-500 hover:text-red-400 ml-1">
                Create one for free &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
