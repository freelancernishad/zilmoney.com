@extends('user.layout')

@section('title', 'Connect Bank')
@section('page_title', 'Banking')
@section('page_subtitle', 'Link your bank account securely via Plaid.')

@section('scripts')
<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass p-8 rounded-[2rem] border-white/5 text-center relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[10%] -right-[10%] w-[50%] h-[50%] bg-indigo-500/10 rounded-full blur-[80px]"></div>
            <div class="absolute -bottom-[10%] -left-[10%] w-[50%] h-[50%] bg-purple-500/10 rounded-full blur-[80px]"></div>
        </div>

        <div class="relative z-10">
            <div class="mb-8 inline-block p-4 rounded-full bg-indigo-500/10 text-indigo-400">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
            </div>
           
            <h2 class="text-2xl font-bold text-white mb-3">Connect Your Bank Account</h2>
            <p class="text-slate-400 mb-8 max-w-md mx-auto">Link your primary bank account to instantly verify your balance and perform transactions on Zilmoney.</p>

            <div id="status-message" class="hidden mb-6 p-4 rounded-xl text-sm font-medium border border-transparent"></div>

            <button id="connect-btn" class="purchase-btn w-full sm:w-auto px-8 py-4 rounded-xl text-white font-bold text-lg shadow-lg flex items-center justify-center gap-3 mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                Launch Plaid Link
            </button>

            <div class="mt-8 flex items-center justify-center gap-2 text-xs text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                <span>Encrypted & Secure Connection</span>
            </div>
        </div>
    </div>

    @if(isset($accounts) && $accounts->count() > 0)
    <div class="mt-8 glass p-8 rounded-[2rem] border-white/5">
        <h3 class="text-xl font-bold text-white mb-6">Linked Accounts</h3>
        <div class="space-y-4">
            @foreach($accounts as $account)
            <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">{{ $account->official_name ?? $account->account_holder_name }}</p>
                        <p class="text-sm text-slate-400 capitalize">{{ $account->type }} •••• {{ $account->mask }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-white font-bold">${{ number_format($account->balance, 2) }}</p>
                    <p class="text-xs text-slate-500">Available</p>
                    @if($account->account_number && $account->routing_number && !str_contains($account->account_number, '*'))
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                            Verified
                        </span>
                    @endif

                    @if($account->plaidItem)
                        @if($account->plaidItem->status === 'login_required')
                             <button onclick="fixConnection('{{ $account->plaidItem->id }}')" class="block mt-2 text-xs text-red-400 hover:text-red-300 font-medium underline">
                                Fix Connection
                            </button>
                        @elseif(config('services.plaid.environment') === 'sandbox')
                             <button onclick="simulateDisconnect('{{ $account->plaidItem->id }}')" class="block mt-2 text-xs text-slate-500 hover:text-slate-300 font-medium underline" title="Test Feature: Force 'Login Required' state">
                                [Test] Simulate Disconnect
                            </button>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Routing Number Validator -->
    <div class="mt-8 p-6 rounded-2xl glass border border-white/5">
        <h3 class="text-xl font-bold text-white mb-4">Validate Routing Number</h3>
        <p class="text-slate-400 text-sm mb-4">Check if a routing number is valid and identifying the bank.</p>
        
        <div class="flex gap-4">
            <input type="text" id="routing-input" placeholder="Enter 9-digit Routing Number" maxlength="9" class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
            <button onclick="validateRouting()" id="validate-btn" class="px-6 py-3 rounded-xl bg-indigo-500 hover:bg-indigo-600 text-white font-bold transition-all">
                Validate
            </button>
        </div>

        <div id="validation-result" class="hidden mt-4 p-4 rounded-xl border">
            <p id="validation-status" class="font-bold text-lg"></p>
            <p id="bank-name" class="text-slate-300 mt-1"></p>
            <p id="bank-location" class="text-slate-400 text-sm"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const connectBtn = document.getElementById('connect-btn');
        // ... (existing code)

        window.validateRouting = async () => {
            const input = document.getElementById('routing-input');
            const btn = document.getElementById('validate-btn');
            const resultDiv = document.getElementById('validation-result');
            const statusText = document.getElementById('validation-status');
            const bankName = document.getElementById('bank-name');
            const bankLocation = document.getElementById('bank-location');

            const rn = input.value.trim();
            if (rn.length !== 9) {
                alert('Routing number must be 9 digits.');
                return;
            }

            btn.disabled = true;
            btn.innerText = 'Checking...';
            resultDiv.classList.add('hidden');

            try {
                const response = await fetch('/api/zilmoney/accounts/validate-routing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ routing_number: rn })
                });

                const data = await response.json();

                resultDiv.classList.remove('hidden', 'bg-emerald-500/10', 'border-emerald-500/20', 'bg-red-500/10', 'border-red-500/20');
                
                if (response.ok && data.valid) {
                    resultDiv.classList.add('bg-emerald-500/10', 'border-emerald-500/20');
                    statusText.innerText = '✅ Valid Routing Number';
                    statusText.className = 'font-bold text-lg text-emerald-400';
                    bankName.innerText = data.bank_name || 'Bank Name Not Found';
                    bankLocation.innerText = data.location || '';
                } else {
                    throw new Error(data.message || 'Invalid Routing Number');
                }

            } catch (error) {
                resultDiv.classList.remove('hidden');
                resultDiv.classList.add('bg-red-500/10', 'border-red-500/20');
                statusText.innerText = '❌ Invalid';
                statusText.className = 'font-bold text-lg text-red-400';
                bankName.innerText = error.message;
                bankLocation.innerText = '';
            } finally {
                btn.disabled = false;
                btn.innerText = 'Validate';
            }
        };

        // ... (existing code)
        const statusMsg = document.getElementById('status-message');

        // Use token from user.layout
        // 'token' variable is global in layout scripts

        connectBtn.addEventListener('click', async () => {
            initiatePlaidLink();
        });

        window.fixConnection = async (itemId) => {
             if (!confirm('You will be redirected to Plaid to update your bank credentials. Continue?')) return;
             initiatePlaidLink(itemId);
        };

        window.simulateDisconnect = async (itemId) => {
             if (!confirm('This will simulate a "Login Required" error from Plaid (Sandbox Only). Continue?')) return;
             
             try {
                const response = await fetch('/api/zilmoney/plaid/reset-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({ item_id: itemId })
                });
                
                if (!response.ok) throw new Error('Failed to reset login');
                
                alert('Login reset! The account should now show "Fix Connection".');
                window.location.reload();

             } catch (e) {
                 alert(e.message);
             }
        };

        async function initiatePlaidLink(itemId = null) {
            if (!token) {
                showStatus('Authentication token missing. Please log in again.', 'error');
                return;
            }

            const btn = document.getElementById('connect-btn'); // Main button status update
            const originalText = btn.innerHTML;
            if(!itemId) {
                 btn.disabled = true;
                 btn.innerHTML = '<span class="animate-pulse">Initializing...</span>';
            } else {
                showStatus('Initializing update mode...', 'success');
            }
            
            try {
                // 1. Get Hosted Link URL
                const body = {
                     redirect_uri: window.location.href // Pass current URL as completion redirect
                };
                if (itemId) {
                    body.item_id = itemId;
                }

                const response = await fetch('/api/zilmoney/plaid/create-link-token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(body)
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to create Link Session');
                }
                
                const responseBody = await response.json();
                console.log('Plaid Response:', responseBody);

                // Handle response wrapper (data.data vs data)
                const payload = responseBody.data || responseBody;

                if (!payload.hosted_link_url) {
                    throw new Error('Hosted Link URL missing from API response. Make sure Hosted Link is enabled in your Plaid Dashboard.');
                }

                // 2. Redirect User to Plaid Hosted UI
                statusMsg.style.display = 'block';
                showStatus('Redirecting to secure banking portal...', 'success');
                
                setTimeout(() => {
                    window.location.href = payload.hosted_link_url;
                }, 1000);

            } catch (error) {
                console.error(error);
                showStatus(error.message, 'error');
                if(!itemId) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }
        }


        function showStatus(msg, type) {
            statusMsg.innerText = msg;
            statusMsg.className = 'hidden mb-6 p-4 rounded-xl text-sm font-medium border'; // flush classes
            
            if (type === 'success') {
                statusMsg.classList.add('bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/20');
            } else {
                statusMsg.classList.add('bg-red-500/10', 'text-red-400', 'border-red-500/20');
            }
            statusMsg.classList.remove('hidden');
        }
    });
</script>
@endsection
