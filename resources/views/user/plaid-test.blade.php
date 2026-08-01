@extends('user.layout')

@section('title', 'Plaid Sandbox Test')
@section('page_title', 'Plaid Sandbox Test Tool')
@section('page_subtitle', 'Simulate checking accounts, check cashing, and transaction events in the Sandbox environment.')

@section('styles')
<style>
    .terminal-window {
        background: rgba(5, 10, 20, 0.7);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.6);
        font-family: 'Courier New', Courier, monospace;
    }
</style>
@endsection

@section('content')
<div id="section-plaid-test" class="content-section space-y-8 pb-12">
    <!-- Main Test Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Configuration & Form Panel -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Connection Check -->
            @if($plaidItems->isEmpty())
                <div class="glass p-8 rounded-[2rem] border-red-500/20 bg-red-500/5 text-center space-y-4">
                    <div class="w-16 h-16 bg-red-500/10 text-red-400 rounded-full flex items-center justify-center mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">No Connected Bank Accounts Found</h3>
                        <p class="text-slate-400 text-sm mt-1">To test transaction and check cashing webhooks, you must connect a Sandbox bank account first.</p>
                    </div>
                    <a href="{{ route('user.connect-bank') }}" class="inline-flex px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all">
                        Connect Bank via Plaid
                    </a>
                </div>
            @endif

            <!-- 1. Selection Card -->
            <div class="glass p-8 rounded-[2rem] border-white/5 space-y-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    Select Plaid Account
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Plaid Item Selector -->
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Connected Bank (Plaid Item)</label>
                        <select id="plaid-item-select" onchange="filterAccounts()" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 transition-all">
                            <option value="">-- Select Bank Item --</option>
                            @foreach($plaidItems as $item)
                                <option value="{{ $item->id }}">ID: {{ $item->id }} (Status: {{ $item->status }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Account Selector -->
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Select Account</label>
                        <select id="account-select" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 transition-all">
                            <option value="">-- Select Account --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->plaid_account_id }}" data-item-id="{{ $acc->plaid_item_id }}" class="account-opt hidden">
                                    {{ $acc->account_nick_name }} ({{ $acc->mask }}) - ${{ number_format($acc->balance, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Step 1: Create Transaction -->
            <div class="glass p-8 rounded-[2rem] border-white/5 space-y-6 relative overflow-hidden">
                <div class="absolute top-6 right-8 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] font-black tracking-widest uppercase">
                    Step 1
                </div>

                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Simulate Check Cashing / Deposit
                    </h3>
                    <p class="text-xs text-slate-400">Add a custom transaction into the selected Plaid Sandbox account.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Amount ($)</label>
                        <input type="number" id="tx-amount" value="250.00" step="0.01" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Transaction Description</label>
                        <input type="text" id="tx-description" value="Cashed Check #12345" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 transition-all">
                    </div>
                </div>

                <button onclick="createTransaction()" id="btn-create-tx" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm transition-all shadow-lg flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Create Sandbox Transaction
                </button>
            </div>

            <!-- 3. Step 2: Fire Webhook -->
            <div class="glass p-8 rounded-[2rem] border-white/5 space-y-6 relative overflow-hidden">
                <div class="absolute top-6 right-8 px-3 py-1 rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/20 text-[10px] font-black tracking-widest uppercase">
                    Step 2
                </div>

                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        Fire Plaid Update Webhook
                    </h3>
                    <p class="text-xs text-slate-400">Trigger Plaid Sandbox to post a webhook alert to your webhook endpoint.</p>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Webhook Code</label>
                    <select id="webhook-code-select" class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:border-indigo-500 transition-all">
                        <option value="DEFAULT_UPDATE">DEFAULT_UPDATE (Standard Transaction Update)</option>
                        <option value="SYNC_UPDATES_AVAILABLE">SYNC_UPDATES_AVAILABLE (New Sync-based Update)</option>
                    </select>
                </div>

                <button onclick="fireWebhook()" id="btn-fire-webhook" class="w-full py-3.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm transition-all shadow-lg flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    Trigger Plaid Webhook
                </button>
            </div>

        </div>

        <!-- Terminal Logging Window -->
        <div class="lg:col-span-5 flex flex-col h-[600px] lg:h-auto">
            <div class="terminal-window rounded-3xl p-6 flex-1 flex flex-col overflow-hidden relative">
                <!-- Dot window controls -->
                <div class="flex items-center justify-between mb-4 border-b border-white/10 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                    </div>
                    <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider">Sandbox Terminal Logs</span>
                    <button onclick="clearTerminal()" class="text-[10px] text-slate-500 hover:text-white font-mono uppercase transition-colors">Clear</button>
                </div>
                
                <!-- Terminal Body Log Content -->
                <div id="terminal-content" class="flex-1 overflow-y-auto font-mono text-xs text-emerald-400/90 space-y-2.5 pr-2">
                    <div class="text-slate-500">[System] Plaid Sandbox Test Console Initialized.</div>
                    <div class="text-slate-500">[System] Ready for sandbox transaction simulations.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function logToTerminal(message, type = 'info') {
        const term = document.getElementById('terminal-content');
        const time = new Date().toLocaleTimeString();
        let colorClass = 'text-emerald-400';
        let prefix = '[INFO]';

        if (type === 'success') {
            colorClass = 'text-green-300 font-bold';
            prefix = '[SUCCESS]';
        } else if (type === 'error') {
            colorClass = 'text-red-400 font-bold';
            prefix = '[ERROR]';
        } else if (type === 'system') {
            colorClass = 'text-indigo-400';
            prefix = '[SYSTEM]';
        } else if (type === 'warn') {
            colorClass = 'text-yellow-400';
            prefix = '[WARNING]';
        }

        const logLine = document.createElement('div');
        logLine.className = `${colorClass} leading-relaxed break-all`;
        logLine.innerHTML = `<span class="text-slate-600">[${time}]</span> <span class="opacity-80">${prefix}</span> ${message}`;
        term.appendChild(logLine);
        term.scrollTop = term.scrollHeight;
    }

    function clearTerminal() {
        const term = document.getElementById('terminal-content');
        term.innerHTML = `<div class="text-slate-500">[System] Console cleared.</div>`;
    }

    function filterAccounts() {
        const itemId = document.getElementById('plaid-item-select').value;
        const accSelect = document.getElementById('account-select');
        const opts = document.querySelectorAll('.account-opt');
        
        accSelect.value = "";
        logToTerminal(`Filtered accounts for Bank Item ID: ${itemId || 'None'}`);

        opts.forEach(opt => {
            if (itemId && opt.getAttribute('data-item-id') === itemId) {
                opt.classList.remove('hidden');
            } else {
                opt.classList.add('hidden');
            }
        });
    }

    async function createTransaction() {
        const plaidItemId = document.getElementById('plaid-item-select').value;
        const plaidAccountId = document.getElementById('account-select').value;
        const amount = document.getElementById('tx-amount').value;
        const description = document.getElementById('tx-description').value;

        if (!plaidItemId || !plaidAccountId) {
            logToTerminal("Please select a Plaid Item and an Account first.", "error");
            return;
        }

        if (!amount || !description) {
            logToTerminal("Amount and description are required.", "error");
            return;
        }

        const btn = document.getElementById('btn-create-tx');
        btn.disabled = true;
        btn.innerHTML = `Creating Transaction...`;

        logToTerminal(`Injecting transaction into account: ${plaidAccountId}...`);
        logToTerminal(`Payload: { amount: $${amount}, description: "${description}" }`);

        try {
            const response = await fetch('/api/zilmoney/plaid/sandbox/create-transaction', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    plaid_item_id: plaidItemId,
                    plaid_account_id: plaidAccountId,
                    amount: amount,
                    description: description
                })
            });

            const result = await response.json();

            if (response.ok && !result.isError) {
                logToTerminal("Transaction created in Plaid Sandbox database successfully!", "success");
                logToTerminal(`Plaid Response: ${JSON.stringify(result.data)}`, "success");
                logToTerminal("Please proceed to Step 2 to fire the webhook and notify the system.", "system");
            } else {
                const errMsg = result.error?.errMsg || result.Message || 'Unknown error';
                logToTerminal(`Failed to create transaction: ${errMsg}`, "error");
            }
        } catch (e) {
            logToTerminal(`API Request Error: ${e.message}`, "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg> Create Sandbox Transaction`;
        }
    }

    async function fireWebhook() {
        const plaidItemId = document.getElementById('plaid-item-select').value;
        const webhookCode = document.getElementById('webhook-code-select').value;

        if (!plaidItemId) {
            logToTerminal("Please select a Plaid Item first.", "error");
            return;
        }

        const btn = document.getElementById('btn-fire-webhook');
        btn.disabled = true;
        btn.innerHTML = `Triggering Webhook...`;

        logToTerminal(`Triggering webhook ${webhookCode} for Plaid Item ID: ${plaidItemId}...`);

        try {
            const response = await fetch('/api/zilmoney/plaid/sandbox/fire-webhook', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    plaid_item_id: plaidItemId,
                    webhook_code: webhookCode
                })
            });

            const result = await response.json();

            if (response.ok && !result.isError) {
                logToTerminal(`Plaid webhook trigger requested successfully.`, "success");
                logToTerminal(`Webhook: ${webhookCode} should be processed by your receiver endpoint shortly.`, "success");
                logToTerminal(`Check your application server log (laravel.log) to see how the webhook was handled.`, "system");
            } else {
                const errMsg = result.error?.errMsg || result.Message || 'Unknown error';
                logToTerminal(`Failed to fire webhook: ${errMsg}`, "error");
            }
        } catch (e) {
            logToTerminal(`API Request Error: ${e.message}`, "error");
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Trigger Plaid Webhook`;
        }
    }
</script>
@endsection
