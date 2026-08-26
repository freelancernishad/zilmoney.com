<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Zilmoney\Account;
use App\Models\Zilmoney\PlaidItem;
use App\Services\Zilmoney\PlaidService;
use App\Services\Zilmoney\BankingService;
use Illuminate\Support\Facades\Log;

class SyncBankLogos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zilmoney:sync-bank-logos {--force : Force update logo even if already present}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resync and backfill bank institution names and logos for all connected bank accounts using Plaid API.';

    /**
     * Execute the console command.
     */
    public function handle(PlaidService $plaidService, BankingService $bankingService)
    {
        $this->info("Starting Bank Logos Sync & Backfill...");
        Log::info("Artisan Command `zilmoney:sync-bank-logos` initiated.");

        $force = $this->option('force');
        
        // 1. Sync via Plaid Items (for Plaid linked accounts)
        $plaidItems = PlaidItem::all();
        $this->info("Processing {$plaidItems->count()} Plaid Item(s)...");

        foreach ($plaidItems as $item) {
            if ($force || empty($item->institution_logo) || empty($item->institution_name)) {
                $this->info("Syncing Plaid Item ID: {$item->id} (Inst ID: {$item->institution_id})...");
                try {
                    $details = $plaidService->syncInstitutionDetails($item);
                    if ($details) {
                        $this->info("  ✓ Updated Item ID {$item->id}: {$details['institution_name']}");
                        Log::info("SyncBankLogos: Updated PlaidItem {$item->id} with logo", $details);
                    } else {
                        $this->warn("  ! Could not fetch details for Item ID {$item->id}");
                    }
                } catch (\Throwable $e) {
                    $this->error("  ✗ Error syncing Item ID {$item->id}: {$e->getMessage()}");
                    Log::error("SyncBankLogos Error on Item {$item->id}: " . $e->getMessage());
                }
            }
        }

        // 2. Sync via Routing Number for Accounts (for manually added or tokenized accounts)
        $accounts = Account::all();
        $this->info("Processing {$accounts->count()} Account(s)...");

        $updatedCount = 0;
        foreach ($accounts as $account) {
            if (($force || empty($account->institution_logo)) && !empty($account->routing_number)) {
                $this->info("Looking up routing {$account->routing_number} for Account ID {$account->id} ({$account->account_holder_name})...");
                try {
                    $plaidDetails = $bankingService->lookupPlaidInstitution($account->routing_number);
                    if ($plaidDetails && !empty($plaidDetails['bank_name'])) {
                        $account->update([
                            'institution_name' => $account->institution_name ?: $plaidDetails['bank_name'],
                            'institution_logo' => $plaidDetails['logo'] ?? $account->institution_logo,
                        ]);
                        $updatedCount++;
                        $this->info("  ✓ Updated Account ID {$account->id}: Bank Name = {$plaidDetails['bank_name']}, Logo = " . (!empty($plaidDetails['logo']) ? 'Yes' : 'No'));
                        Log::info("SyncBankLogos: Updated Account {$account->id}", [
                            'routing' => $account->routing_number,
                            'bank_name' => $plaidDetails['bank_name'],
                            'logo_attached' => !empty($plaidDetails['logo']),
                        ]);
                    } else {
                        $this->warn("  ! No Plaid institution found for routing {$account->routing_number}");
                    }
                } catch (\Throwable $e) {
                    $this->error("  ✗ Error for Account ID {$account->id}: {$e->getMessage()}");
                    Log::error("SyncBankLogos Error on Account {$account->id}: " . $e->getMessage());
                }
            }
        }

        $this->info("Sync Completed! Total Accounts Updated: {$updatedCount}. Check laravel.log for details.");
        return Command::SUCCESS;
    }
}
