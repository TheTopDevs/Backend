<?php

namespace App\Services;

use App\Models\Lister;
use App\Models\ListerInfo;
use App\Models\Portfolio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class ListerService
{

    public function listerPortfolioStockExist(int $listerId): bool
    {
        return Portfolio::query()->where('lister_id', $listerId)->exists();
    }

    public function updateOrCreateLister(int $userId, array $params): Lister
    {
        return Lister::updateOrCreate(
            ['user_id' => $userId],
            array_merge($params, ['user_id' => $userId])
        );
    }

    /** Admin assign new rating for lister */
    public function assignNewRating(Lister $lister, string $rating)
    {
        $lister->rating = $rating;
        $lister->save();
    }

    public function assignInitialListPrice(Lister $lister, float $price)
    {
        $lister->init_sale_price = $price;
        $lister->save();
    }

    public function getListerForUser(int $userId): Lister
    {
        return Lister::query()->where('user_id', $userId)->firstOrFail();
    }

    public function getPortfolioStocksForLister(int $listerId): Collection
    {
        return Portfolio::query()
            ->with(['lister', 'user'])
            ->where('amount', '>', 0)
            ->where('lister_id', $listerId)
            ->get();
    }
}
