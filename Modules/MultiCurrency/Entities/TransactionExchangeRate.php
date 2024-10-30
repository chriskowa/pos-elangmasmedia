<?php

namespace Modules\MultiCurrency\Entities;

use Illuminate\Database\Eloquent\Model;

class TransactionExchangeRate extends Model
{
    // protected $fillable = [];

    protected $guarded = ['id'];
    
    /**
     * Get the currency exchange rate related to this payment.
     */
    public function currency_exchange_rate()
    {
        return $this->belongsTo(\App\Transaction::class, 'transaction_id');
    }
}
