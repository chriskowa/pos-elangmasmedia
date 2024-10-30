<?php

namespace Modules\MultiCurrency\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Routing\Events\RouteMatched;

class RouteMatchedHandler
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param RouteMatched $event
     * @return void
     */
    public function handle(RouteMatched $event)
    {
        $routesToInject = [
            //'sells.create', 'sells.edit', 'sells.show',
            'purchase-order.create', 'purchase-order.edit', 'purchase-order.show', 'purchase-order.index',
            'purchases.create', 'purchases.edit', 'purchases.show', 'purchases.index',
            //'pos.create', 'pos.edit', 'pos.show',
        ];

        if (in_array($event->route->getName(), $routesToInject)) {
            $event->route->middleware('inject-content');
        }
    }
}
