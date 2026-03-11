<?php

use App\Core\View;
use App\Helpers\FlashSession;
use App\Core\Csrf;

View::extend('layouts/auth');
?>

<?php View::section('content'); ?>
<div class="w-full max-w-[400px] mx-auto px-6">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="text-center mb-8">
            <div class="h-12 w-12 bg-blue-600 text-white rounded-lg mx-auto grid place-items-center mb-4 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-800 tracking-tight">Create Account</h3>
            <p class="text-slate-400 font-semibold text-[11px] uppercase tracking-widest mt-2">Join the CMS & Framework Ecosystem</p>
        </div>

        <div class="warning-container mb-4">
            <?php if ($msg = FlashSession::get('flash_error')): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg border border-red-100 font-bold text-center italic text-xs">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <?php if ($msg = FlashSession::get('csrf_error')): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg border border-red-100 font-bold text-center italic text-xs">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="/register" method="POST" class="space-y-5">
            <input type="text" name="_token" hidden value="<?= Csrf::token() ?>">

            <div class="space-y-2">
                <label class="text-slate-600 font-bold text-xs uppercase tracking-wider" for="name">
                    Full Name
                </label>
                <input placeholder="Your Name" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-lg focus:border-blue-500 focus:bg-white focus:outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300 text-sm" type="text" name="name" id="name">
            </div>

            <div class="space-y-2">
                <label class="text-slate-600 font-bold text-xs uppercase tracking-wider" for="email">
                    Email Address
                </label>
                <input placeholder="name@company.com" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-lg focus:border-blue-500 focus:bg-white focus:outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300 text-sm" type="email" name="email" id="email">
            </div>

            <div class="space-y-2">
                <label class="text-slate-600 font-bold text-xs uppercase tracking-wider" for="password">
                    Password
                </label>
                <input placeholder="••••••••" required class="w-full bg-slate-50 border border-slate-200 px-4 py-3 rounded-lg focus:border-blue-500 focus:bg-white focus:outline-none transition-all font-medium text-slate-700 placeholder:text-slate-300 text-sm" type="password" name="password" id="password">
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-bold rounded-lg shadow-sm hover:bg-blue-700 active:scale-[0.98] transition-all text-sm">
                Sign Up
            </button>
        </form>
        
        <div class="text-center mt-6">
            <p class="text-xs text-slate-400 font-medium">Already have an account? <a href="/login" class="text-blue-600 font-bold hover:underline">Login here</a></p>
        </div>
    </div>

    <div class="text-center mt-8">
        <a href="/" class="text-slate-400 font-bold text-xs hover:text-blue-600 transition-colors flex items-center justify-center gap-2 uppercase tracking-widest">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Home
        </a>
    </div>
</div>
<?php View::endSection(); ?>
