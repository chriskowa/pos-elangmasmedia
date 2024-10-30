<?php

namespace Modules\MultiCurrency\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
     /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'multicurrency.access_package_subscriptions',
                'label' => __('multicurrency::lang.access_package_subscriptions'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds MultiCurrency menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        if (auth()->user()->can('multicurrency.access_package_subscriptions') && auth()->user()->can('business_settings.access')) {
            $menu = Menu::instance('admin-sidebar-menu');
            $menu->whereTitle(__('business.settings'), function ($sub) {
                $sub->url(
                    action([\Modules\MultiCurrency\Http\Controllers\MultiCurrencyController::class, 'index']),
                    __('multicurrency::lang.manageMultiCurrency'),
                    ['active' => request()->segment(1) == 'multi_currency_settings']
                );
            });
        }
    }
}
