<?php

namespace App\Models;

use App\Enums\PayMethodEnum;
use App\Enums\TransactionStatusEnum;
use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Freefunds extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function isInsufficientFunds($amount)
    {
        $freefunds = Freefunds::lockForUpdate()
            ->where('id', '=', $this->id)
            ->where('amount', '>=', $amount)
            ->first();

        if (! $freefunds instanceof Freefunds) {
            throw new \Exception('Insufficient funds.');
        }

        return false;
    }

    public function withdraw($amount)
    {
        $transaction = new FreefundsTransaction();
        $transaction->user_id = $this->user_id;
        $transaction->instrument_id = $this->instrument_id;
        $transaction->amount = $amount;
        $transaction->type = TransactionTypeEnum::WITHDRAW;
        $transaction->status = TransactionStatusEnum::ACCEPTED;
        $transaction->pay_method = PayMethodEnum::INTERNAL;
        $transaction->save();

        $this->amount = bcsub($this->amount, $amount, 8);
        $this->save();
    }

    public function replenish($amount)
    {
        $transaction = new FreefundsTransaction();
        $transaction->user_id = $this->user_id;
        $transaction->instrument_id = $this->instrument_id;
        $transaction->amount = $amount;
        $transaction->type = TransactionTypeEnum::REPLENISH;
        $transaction->status = TransactionStatusEnum::ACCEPTED;
        $transaction->pay_method = PayMethodEnum::INTERNAL;
        $transaction->save();

        $this->amount = bcadd($this->amount, $amount, 8);
        $this->save();
    }
}
