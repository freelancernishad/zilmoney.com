<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insufficient Credit Balance | Zil Money</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#0F172A] text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-slate-900/90 border border-slate-800 rounded-3xl p-8 shadow-2xl backdrop-blur-xl relative overflow-hidden text-center">
        <!-- Decorative Glow -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Icon -->
        <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-tr from-amber-500/20 to-amber-400/10 border border-amber-500/30 rounded-2xl flex items-center justify-center text-amber-400 shadow-lg shadow-amber-500/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-extrabold text-white tracking-tight mb-2">
            Insufficient Credit Balance
        </h1>
        <p class="text-xs text-slate-400 leading-relaxed mb-6">
            You do not have enough credits in your wallet to complete <span class="text-amber-400 font-semibold">{{ $serviceName }}</span>. Please top up your balance to proceed.
        </p>

        <!-- Breakdown Card -->
        <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-4 mb-6 space-y-3 text-left">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-400 font-medium">Service Fee Required</span>
                <span class="font-bold text-amber-400">${{ $servicePrice }} USD</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-400 font-medium">Your Available Credit</span>
                <span class="font-bold text-red-400">${{ $availableCredit }} USD</span>
            </div>
            <div class="pt-2 border-t border-slate-700/60 flex items-center justify-between text-xs">
                <span class="text-slate-400 font-medium">Current Active Plan</span>
                <span class="font-bold text-slate-200">{{ $planName }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="{{ $rechargeUrl }}" 
               class="w-full py-3.5 px-6 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold rounded-xl text-sm transition-all duration-200 shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 block">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Recharge Credits Now
            </a>

            <button onclick="window.close()" 
                    class="w-full py-3 px-6 bg-slate-800/80 hover:bg-slate-800 text-slate-400 hover:text-slate-200 font-semibold rounded-xl text-xs transition-colors border border-slate-700/50 block w-full mt-2">
                Close Window
            </button>
        </div>

        <!-- Footer -->
        <p class="text-[10px] text-slate-500 mt-6">
            Powered by Zil Money • Secure Credit Payment Gateway
        </p>
    </div>

</body>
</html>
