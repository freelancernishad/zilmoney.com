<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AddUserCreditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add-credit 
                            {email : The email of the user} 
                            {amount : Credit amount to add or set} 
                            {--set : Set exact balance instead of adding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or set credit balance for a specific user by email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $amount = (float) $this->argument('amount');
        $isSetMode = $this->option('set');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return Command::FAILURE;
        }

        $oldBalance = (float) ($user->credit_balance ?? 0);

        if ($isSetMode) {
            $newBalance = $amount;
        } else {
            $newBalance = $oldBalance + $amount;
        }

        $user->credit_balance = $newBalance;
        $user->save();

        $action = $isSetMode ? 'set to' : 'updated to';
        $this->info("Success! User '{$user->name}' ({$email}) credit balance {$action} {$newBalance} (Previous: {$oldBalance}).");

        return Command::SUCCESS;
    }
}
