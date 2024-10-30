<?php

namespace Modules\MultiCurrency\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Modules\MultiCurrency\Entities\TransactionExchangeRate;

class MultiCurrencyTransaction
{
    protected $request;

    /**
     * Create the event listener.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $requestData = $this->request->all();
        if($requestData['m_currency_id'] && $requestData['m_exchange_rate']){
            $transaction_id = $event->transaction->id;
            $currency_transaction = TransactionExchangeRate::updateOrCreate(
                ['transaction_id' => $transaction_id],
                [
                    'currency_id' => $requestData['m_currency_id'],
                    'exchange_rate' => $requestData['m_exchange_rate'],
                ]
            );
            return $currency_transaction;
        }
    }
}
