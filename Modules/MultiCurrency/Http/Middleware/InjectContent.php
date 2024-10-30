<?php

namespace Modules\MultiCurrency\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\MultiCurrency\Entities\MultiCurrencySetting;
use Modules\MultiCurrency\Entities\TransactionExchangeRate;

class InjectContent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $routeName = $request->route()->getName();

        if ($response instanceof \Illuminate\Http\Response && strpos($response->headers->get('Content-Type'), 'text/html') !== false) {
            $content = $response->getContent();
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            $xpath = new \DOMXPath($dom);
            $divs = $xpath->query('//form');

            if ($divs->length > 0) {
                $targetDiv = $divs->item(0);
                $business_id = request()->session()->get('user.business_id');
                $currencies = MultiCurrencySetting::where('business_id', $business_id)
                                ->join('currencies as c', 'multi_currency_settings.currency_id', '=', 'c.id')
                                ->select('c.id', DB::raw("concat(c.country, ' - ', c.currency, '(', c.code, ') ') as info"))
                                ->orderBy('c.country')
                                ->pluck('info', 'c.id');
                //get the transaction currency rate 
                $editPages = ['purchase-order.edit','purchases.edit'];

                if (in_array($routeName, $editPages)) {
                    // Retrieve transaction values and pass them to the view
                    $id = array_keys($request->route()->parameters())[0];
                    $transaction_id = $request->route()->parameter($id);

                    $currency_transaction = TransactionExchangeRate::where('transaction_id', $transaction_id)->firstOrFail();
                } else {
                    $currency_transaction = [];
                }

                //inject html code inside all pages except show pages
                $showPages = ['purchase-order.index', 'purchases-order.show','purchases.index', 'purchases.show'];
                if (!in_array($routeName, $showPages)) {
                    $viewHtml = view('multicurrency::inject.html', [
                        'currencies' => $currencies,
                        'currency_transaction' => $currency_transaction,
                    ])->render();

                    $tempDom = new \DOMDocument();
                    @$tempDom->loadHTML('<?xml encoding="utf-8" ?>' . $viewHtml);
                    $importedNode = $dom->importNode($tempDom->documentElement, true);
                    $targetDiv->insertBefore($importedNode, $targetDiv->firstChild);
                }

                //inject js specificly for purccgases pages
                $purchasePages = ['purchase-order.create', 'purchases-order.edit', 'purchases-order.show', 'purchases-order.index', 'purchases.create', 'purchases.edit', 'purchases.show', 'purchases.index'];
                if (in_array($routeName, $purchasePages)) {
                    $viewJs = view('multicurrency::inject.js-purchase')->render();
                    $scriptTag = $dom->createElement('script');
                    $scriptTag->setAttribute('type', 'text/javascript');
                    $scriptTag->nodeValue = $viewJs;
                    $body = $dom->getElementsByTagName('body')->item(0);
                    $body->appendChild($scriptTag);
                }

                //inject js specificly for sells & pos pages
                $sellPages = [
                    //'sells.create', 'sells.edit', 'sells.show', 'sells.index',
                    //'pos.create', 'pos.edit', 'pos.show', 'pos.index'
                ];
                if (in_array($routeName, $sellPages)) {
                    $viewJs = view('multicurrency::inject.js-sell')->render();
                    $scriptTag = $dom->createElement('script');
                    $scriptTag->setAttribute('type', 'text/javascript');
                    $scriptTag->nodeValue = $viewJs;
                    $body = $dom->getElementsByTagName('body')->item(0);
                    $body->appendChild($scriptTag);
                }

                // Save the modified HTML back to the response content
                $newContent = $dom->saveHTML();
                $response->setContent($newContent);
            }
        }

        return $response;
    }
}
