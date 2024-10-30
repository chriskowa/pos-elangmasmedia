<?php

namespace Modules\MultiCurrency\Http\Controllers;

use App\Currency;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Datatables;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\MultiCurrency\Entities\MultiCurrencySetting;

class MultiCurrencyController extends Controller
{
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $currencies = MultiCurrencySetting::where('business_id', $business_id)
                        ->leftjoin(
                            'currencies as c',
                            'multi_currency_settings.currency_id',
                            '=',
                            'c.id'
                        )
                        ->select('multi_currency_settings.id',DB::raw("concat(c.country, ' - ',c.currency, '(', c.code, ') ') as currency"),'exchange_rate','type');

            return Datatables::of($currencies)
                ->addColumn(
                    'action',
                    '<button type="button" data-href="{{action(\'\Modules\MultiCurrency\Http\Controllers\MultiCurrencyController@edit\', [$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".multicurrency_edit_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                    &nbsp;
                    <button data-href="{{action(\'Modules\MultiCurrency\Http\Controllers\MultiCurrencyController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_m_currency_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    '
                )
                ->removeColumn('id')
                ->rawColumns([3])
                ->make(false);
        }

        return view('multicurrency::settings.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $currencies = $this->businessUtil->allCurrencies();
        return view('multicurrency::settings.create')
        ->with(compact(
            'currencies'
        ));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['currency_id', 'exchange_rate', 'type']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['business_currency_id'] = $request->session()->get('business.currency_id');
            $input['type'] = (isset($input['type']) && $input['type']==1)?'api':'fixed';

            $currency = MultiCurrencySetting::create($input);
            $output = ['success' => true,
                'msg' => __('multicurrency::lang.currency_rate_added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line: '.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('multicurrency::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $currency = MultiCurrencySetting::where('business_id', $business_id)
                                    ->find($id);
        $currencies = $this->businessUtil->allCurrencies();
        return view('multicurrency::settings.edit')
        ->with(compact(
            'currencies',
            'currency'
        ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['currency_id', 'exchange_rate', 'type']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['business_currency_id'] = $request->session()->get('business.currency_id');
            $input['type'] = ($input['type']==1)?'api':'fixed';

            $currency = MultiCurrencySetting::where('id', $id)->update($input);
            $output = ['success' => true,
                'msg' => __('multicurrency::lang.currency_rate_updated_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().' Line: '.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $currency = MultiCurrencySetting::find($id);
                $currency->delete();
                $output = ['success' => true,
                    'msg' => __('multicurrency::lang.currency_rate_deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().' Line: '.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function exchangeRate(Request $request, $id){
        $business_id = request()->session()->get('user.business_id');
        $currency = MultiCurrencySetting::where('business_id', $business_id)
                                        ->where('multi_currency_settings.currency_id', $id)
                                        ->join('currencies', 'multi_currency_settings.currency_id', '=', 'currencies.id')
                                        ->select('multi_currency_settings.*', 'currencies.*')
                                        ->first();
        if ($currency) {
            return response()->json([
                'success' => true,
                'data' => $currency
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }
    }

    public function apiCurrenyRate($id) {
        // Access currency ID and code from the session
        $currency_id = request()->session()->get('business.currency_id');
        $currency_code = request()->session()->get('business.currency.code');

        // Retrieve the currency code from the database using the provided ID
        $codeCurrIso = Currency::where('id', $id)->value('code');

        if (empty($codeCurrIso)) {
            \Log::emergency('No currency found with ID: ' . $id);
            return response()->json(['error' => 'No currency found'], 404);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.fxratesapi.com/latest?base=".$codeCurrIso."&currencies=".$currency_code."&places=2",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            \Log::emergency('cURL Error: ' . $error);
            curl_close($curl);
            return response()->json(['error' => 'cURL error: ' . $error], 500);
        }

        $responseData = json_decode($response, true);
        $responseData['code'] = $currency_code;

        return response()->json($responseData);
    }


}
