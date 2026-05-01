<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'The Commission Apparel | Elite Custom Uniforms')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base text-slate-900 font-sans antialiased selection:bg-primary selection:text-white">
    <header class="fixed top-0 left-0 w-full z-50 bg-black backdrop-blur-xl border-b border-slate-800 transition-all duration-300" x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = (window.pageYOffset > 20)" :class="{ 'py-1.5 shadow-sm': scrolled, 'py-2.5': !scrolled }">
        <div class="max-w-[1500px] mx-auto px-6 flex items-center justify-between transition-all duration-300" :class="{ 'h-12': scrolled, 'h-14 md:h-16': !scrolled }">
            <!-- Logo -->
            <a href="/" class="flex items-center group z-50 relative">
                <img src="/images/New%20Logo.png" alt="The Commission Apparel Logo" class="h-10 md:h-[2.2rem] w-auto group-hover:opacity-80 transition-opacity">
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-8 font-medium text-sm tracking-wide">
                <a href="{{ route('catalog.index') }}" class="text-white hover:text-secondary transition-colors">Design Collections</a>
                <a href="{{ route('store.search') }}" class="text-white hover:text-secondary transition-colors">Team Stores</a>
                <a href="/quote" class="text-white hover:text-secondary transition-colors">Request A Quote</a>
                <div class="h-6 w-px bg-slate-700"></div>
                <a href="/coach/dashboard" class="relative group py-2 px-5 text-xs font-bold tracking-wider uppercase overflow-hidden border border-slate-700 text-white bg-secondary/5 hover:bg-secondary/10 transition-all rounded-md shadow-sm">
                    <span class="relative z-10 flex items-center gap-2">
                        <img src="/images/LR.png" alt="LR Logo" class="h-4 w-auto object-contain">
                        Dashboard Sign-in
                    </span>
                </a>
            </nav>

            <!-- Mobile Menu Button -->
            <button class="lg:hidden z-50 relative p-2 text-white hover:text-secondary focus:outline-none" @click="mobileMenuOpen = !mobileMenuOpen">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Mobile Navigation Dropdown -->
        <div x-show="mobileMenuOpen" x-transition.opacity.duration.200ms class="lg:hidden fixed inset-0 z-40 bg-white/95 backdrop-blur-3xl pt-28 px-6 pb-6 h-screen overflow-y-auto" style="display: none;" x-cloak>
            <nav class="flex flex-col gap-6 font-medium text-lg tracking-wide uppercase">
                <a href="{{ route('catalog.index') }}" class="text-slate-900 hover:text-secondary transition-colors border-b border-slate-200 pb-4" @click="mobileMenuOpen = false">Design Collections</a>
                <a href="{{ route('store.search') }}" class="text-slate-900 hover:text-secondary transition-colors border-b border-slate-200 pb-4" @click="mobileMenuOpen = false">Team Stores</a>
                <a href="/quote" class="text-slate-900 hover:text-secondary transition-colors border-b border-slate-200 pb-4" @click="mobileMenuOpen = false">Request A Quote</a>
                <div class="flex flex-col gap-4 mt-4">
                    <a href="/coach/dashboard" class="btn border border-slate-300 text-slate-800 bg-white hover:bg-slate-50 py-4 px-4 flex items-center justify-center gap-2 text-center font-bold text-sm tracking-wider uppercase shadow-sm" @click="mobileMenuOpen = false">
                        <img src="/images/LR.png" alt="LR Logo" class="h-5 w-auto object-contain">
                        Dashboard Sign-in
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="w-full relative mt-16 lg:mt-0"> <!-- lg mt 0 because hero is absolutely positioned and taking full screen -->
        @yield('content')
    </main>

    <footer class="bg-slate-950 border-t border-slate-800 py-12 lg:py-16 relative overflow-hidden">
        <div class="absolute bottom-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-primary to-transparent opacity-50"></div>
        <div class="max-w-[1500px] mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12 relative z-10">
            <div class="col-span-1 md:col-span-2">
                <div class="mb-6 mix-blend-screen">
                    <img src="/images/New%20Logo.png" alt="The Commission Apparel Logo" class="h-14 w-auto mix-blend-screen">
                </div>
                <p class="text-slate-400 max-w-sm mb-6">Elite Custom Uniforms for Teams Worldwide. We build powerful visual identities for programs that expect to win.</p>
                <div class="flex gap-4">
                    <!-- Social icons -->
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-secondary/20 hover:text-secondary text-slate-300 transition-all"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-secondary/20 hover:text-secondary text-slate-300 transition-all"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider">Platform</h4>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="#" class="hover:text-secondary transition-colors">Team Stores</a></li>
                    <li><a href="/coach/dashboard" class="hover:text-secondary transition-colors">Coach Portal</a></li>
                    <li><a href="#" class="hover:text-secondary transition-colors">Employee Login</a></li>
                    <li><a href="#" class="hover:text-secondary transition-colors">Track Order</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="hover:text-secondary transition-colors">Design Collection</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase tracking-wider">Company</h4>
                <ul class="space-y-2 text-slate-400">
                    <li><a href="#" class="hover:text-secondary transition-colors">Our Story</a></li>
                    <li><a href="#" class="hover:text-secondary transition-colors">Global Presence</a></li>
                    <li><a href="#" class="hover:text-secondary transition-colors">Fabrics & Materials</a></li>
                    <li><a href="#" class="hover:text-secondary transition-colors">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-[1500px] mx-auto px-6 mt-12 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} The Commission Apparel. All rights reserved.
        </div>
    </footer>
    
    @auth
        <x-notification-toast />
    @endauth

    <!-- Global Form Submit Loading State -->
    <script>
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.nodeName === 'FORM') {
                const btn = e.target.querySelector('button[type="submit"]') || e.target.querySelector('input[type="submit"]');
                if (btn && !btn.dataset.loadingIgnored) {
                    if (btn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    
                    btn.disabled = true;
                    btn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
                    
                    const hasText = btn.innerText && btn.innerText.trim().length > 0;
                    const spinnerSvg = `<svg class="animate-spin h-5 w-5 text-current inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
                    
                    if (hasText) {
                        btn.innerHTML = `<span class="flex items-center justify-center gap-2">${spinnerSvg} Processing...</span>`;
                    } else {
                        btn.innerHTML = `<span class="flex items-center justify-center">${spinnerSvg}</span>`;
                    }
                }
            }
        });
    </script>
</body>
</html>
