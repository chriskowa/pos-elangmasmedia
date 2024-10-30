<BR/>
@component('components.widget', ['class' => 'box-solid'])
<div class="row">
    <div class="col-sm-4" id="settings_multi_currency_div">
        <div class="form-group">
            {!! Form::label('m_currency_id', __('multicurrency::lang.currency') . ':') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fas fa-money-bill-alt"></i>
                </span>
                {!! Form::select('m_currency_id', $currencies, $currency_transaction->currency_id ?? null, ['class' => 'form-control select2', 'placeholder' => __('multicurrency::lang.currency'), 'style' => 'width:100% !important']); !!}
            </div>
        </div>
    </div>
    <div class="col-sm-4" id="settings_multi_currency_exchange_div">
        <div class="form-group">
            {!! Form::label('m_exchange_rate', __('multicurrency::lang.exchange_rate') . ':') !!}
            @show_tooltip(__('multicurrency::lang.tooltip_currency_exchange_factor'))
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-info"></i>
                </span>
                {!! Form::number('m_exchange_rate', $currency_transaction->exchange_rate ?? null, ['class' => 'form-control', 'placeholder' => __('multicurrency::lang.exchange_rate'), 'step' => '0.001', 'readonly' => 'readonly']); !!}
            </div>
        </div>
    </div>
    <div>
        <!-- Page multi currency setting -->
        <input type="hidden" id="m_code">
        <input type="hidden" id="m_symbol">
        <input type="hidden" id="m_thousand">
        <input type="hidden" id="m_decimal">
    </div>
</div>
@endcomponent