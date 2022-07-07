<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionBuilder
{
    public function __construct(private Transaction $transaction)
    {
    }

    private function clear()
    {
        $this->transaction->refresh();
    }

    public function setListerId(int $listerId)
    {
        $this->transaction->listers_id = $listerId;
        return $this;
    }

    public function setTransferFrom(int $userFromId)
    {
        $this->transaction->transfer_from = $userFromId;
        return $this;
    }

    public function setTransferTo(int $userToId)
    {
        $this->transaction->transfer_to = $userToId;
        return $this;
    }

    public function setShards(int $amount)
    {
        $this->transaction->shards = $amount;
        return $this;
    }

    public function setUnitPrice(float $price)
    {
        $this->transaction->unit_price = $price;
        return $this;
    }

    public function execute(): bool
    {
        $this->transaction->total = $this->transaction->unit_price * $this->transaction->shards;
        //TODO make calculate FEE
        $this->transaction->fees = 0;
        //TODO make connect with AWS blockchain
        $this->transaction->block_hash = sha1(time());
        $this->transaction->sequence_numbers = md5(time());

        $result = $this->transaction->saveOrFail();

        //minus portfolio-stock balance in user_from
        //for decrement operation use (*-1)
        $dcrAmount = $this->transaction->shards * -1;
        $this->updateStockPortfolioLister(
            $this->transaction->listers_id,
            $this->transaction->transfer_from,
            $dcrAmount,
            $this->transaction->unit_price
        );

        //plus portfolio-stock user balance in transfer_to
        $this->updateStockPortfolioLister(
            $this->transaction->listers_id,
            $this->transaction->transfer_to,
            $this->transaction->shards,
            $this->transaction->unit_price
        );
        $this->clear();
        return $result;
    }

    protected function updateStockPortfolioLister(int $listerId, int $userId, int $amount, float $price): Portfolio
    {
        $amountOperation = ($amount > 0) ? DB::raw("amount + {$amount}") : DB::raw("amount {$amount}");
        return Portfolio::updateOrCreate(
            ['lister_id' => $listerId, 'user_id' => $userId],
            [
                'lister_id' => $listerId,
                'user_id' => $userId,
                'amount' => $amountOperation,
                'last_purchase_price' => $price
            ]
        );
    }
}
