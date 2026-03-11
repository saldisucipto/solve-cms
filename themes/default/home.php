<?php

use App\Core\View;

View::extend('layouts/front');
?>

<?php View::section('content'); ?>
<div class="relative overflow-hidden pt-16 pb-20 lg:pt-24 lg:pb-32 bg-white">
    <!-- Hero Section -->
    <div class="container mx-auto px-6 relative z-10">
        <div class="max-w-3xl mx-auto text-center">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 font-bold text-[11px] uppercase tracking-widest mb-6">
                🚀 Welcome to the Future of PHP
            </div>
            <h1 class="text-4xl lg:text-6xl font-bold text-slate-900 leading-tight mb-6">
                Simple CMS & <span class="text-blue-600">Framework</span>
            </h1>
            <p class="text-lg lg:text-xl text-slate-500 font-medium max-w-2xl mx-auto leading-relaxed mb-10">
                A lightweight <span class="text-slate-900 font-semibold">Content Management System</span> designed to
                evolve into a full-scale <span class="text-slate-900 font-semibold">PHP Framework</span> for modern web
                applications.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/login"
                    class="px-6 py-3 bg-blue-600 text-white font-bold rounded-lg shadow-sm hover:bg-blue-700 transition-all duration-200 text-sm min-w-[160px]">
                    Get Started
                </a>
                <a href="#about"
                    class="px-6 py-3 bg-white text-slate-600 font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition-all duration-200 text-sm min-w-[160px]">
                    Learn More
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div id="about" class="py-20 bg-slate-50 border-y border-slate-100">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div
                    class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-600 font-bold text-[10px] uppercase tracking-widest">
                    Our Vision
                </div>
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 leading-tight">
                    Clean architecture. <br>
                    <span class="text-blue-600">Developer focus.</span>
                </h2>
                <p class="text-slate-500 font-medium leading-relaxed">
                    Solve CMS is built on principles of simplicity and performance. We focus on providing the essentials
                    without the bloat, allowing you to scale from a simple site to a complex application.
                </p>

                <div class="space-y-4 pt-4">
                    <div class="flex gap-4">
                        <div
                            class="flex-shrink-0 h-5 w-5 rounded-full bg-blue-100 text-blue-600 grid place-items-center mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Lightweight Core</h4>
                            <p class="text-slate-500 font-medium text-xs">Pure, optimized PHP code with minimal
                                dependencies.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex-shrink-0 h-5 w-5 rounded-full bg-blue-100 text-blue-600 grid place-items-center mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Modular Design</h4>
                            <p class="text-slate-500 font-medium text-xs">Easily extend functionality with plugins and
                                custom logic.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div
                        class="h-10 w-10 rounded-lg bg-slate-50 text-slate-600 grid place-items-center mb-4 border border-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 012-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Modern CMS</h3>
                    <p class="text-slate-500 font-medium text-xs leading-relaxed">Fast and intuitive content management
                        for any scale.</p>
                </div>
                <div class="p-6 rounded-xl bg-white border border-slate-200 shadow-sm">
                    <div
                        class="h-10 w-10 rounded-lg bg-slate-50 text-slate-600 grid place-items-center mb-4 border border-slate-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Framework</h3>
                    <p class="text-slate-500 font-medium text-xs leading-relaxed">A solid foundation for building
                        complex web applications.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>