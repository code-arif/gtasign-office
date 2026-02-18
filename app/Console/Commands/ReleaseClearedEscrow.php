<?php


namespace App\Console\Commands;

use App\Services\Payment\EscrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Releases earnings that have passed their 14-day clearing period.
 * Moves SellerEarning from "clearing" → "available"
 * Updates seller's available_balance.
 *
 * Schedule: daily
 */
class ReleaseClearedEscrow extends Command
{
    protected $signature   = 'escrow:release';
    protected $description = 'Release seller earnings that have passed their 14-day clearing period';

    public function __construct(protected EscrowService $escrowService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Releasing cleared escrow earnings...');

        try {
            $count = $this->escrowService->releaseClearedEarnings();
            $this->info("Released {$count} earning(s).");
            Log::info("Escrow release: {$count} earnings released");
        } catch (\Exception $e) {
            $this->error('Escrow release failed: ' . $e->getMessage());
            Log::error('Escrow release command failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}


// ============================================================
// FILE 2: app/Console/Commands/ExpireCustomOffers.php
// ============================================================
// (Keep in a separate file in your actual project)

// namespace App\Console\Commands;

// use App\Models\CustomOffer;
// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Log;

// /**
//  * Marks custom offers as expired if their expiry date has passed.
//  * Schedule: hourly
//  */
// class ExpireCustomOffers extends Command
// {
//     protected $signature   = 'offers:expire';
//     protected $description = 'Mark expired custom offers';

//     public function handle(): int
//     {
//         $count = CustomOffer::where('status', 'pending')
//             ->where('expires_at', '<=', now())
//             ->update(['status' => 'expired']);

//         $this->info("Expired {$count} offer(s).");
//         return self::SUCCESS;
//     }
// }
