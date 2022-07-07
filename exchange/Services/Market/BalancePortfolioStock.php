<?php

namespace App\Services\Market;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalancePortfolioStock
{
    private int $total;
    private int $amountInOffers;
    private int $availableForSell;


    public function __construct(private int $userId, private int $listerId)
    {
        $portfolioBalance = $this->getAmountPortfolioStockForUser($this->userId, $this->listerId)->first();
        $this->total = $portfolioBalance->amount;
        $this->amountInOffers = $portfolioBalance->reserved_shares;
        $this->availableForSell = $portfolioBalance->available;
    }

    private function getAmountPortfolioStockForUser(int $userId, int $listerId): Collection
    {
        return DB::table('portfolios')
            ->where('user_id', $userId)
            ->where('lister_id', $listerId)
            ->limit(1)
            ->get(['amount', 'reserved_shares', DB::raw('(amount-reserved_shares) as available')]);
    }

    public static function make(int $listerId, int $userId = 0): self
    {
        $userIdBalance = (empty($userId)) ? Auth::user()->id : $userId;
        $balance = app()->make(__CLASS__, ['listerId' => $listerId, 'userId' => $userIdBalance]);
        return $balance;
    }

    public function getTotalAmount(): int
    {
        return $this->total;
    }

    public function getReservedAmount(): int
    {
        return $this->amountInOffers;
    }

    public function getAvailableAmount(): int
    {
        return $this->availableForSell;
    }
}
