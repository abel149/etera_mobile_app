<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Process a transaction for a user.
     *
     * @param User $user
     * @param float $amount Positive for credit, Negative for debit
     * @param string $type
     * @param string $description
     * @param Model|null $reference
     * @param array|null $meta
     * @return Transaction
     */
    public function processTransaction(User $user, float $amount, string $type, string $description, $reference = null, $meta = null)
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $reference, $meta) {
            // Lock user row for update to prevent race conditions
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $newBalance = $user->wallet_balance + $amount;

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference ? $reference->id : null,
                'description' => $description,
                'meta' => $meta,
            ]);

            $user->update(['wallet_balance' => $newBalance]);

            return $transaction;
        });
    }

    /**
     * Get user balance.
     */
    public function getBalance(User $user)
    {
        return $user->wallet_balance;
    }
}
