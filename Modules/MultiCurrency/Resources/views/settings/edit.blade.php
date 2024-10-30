<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\MultiCurrency\Http\Controllers\MultiCurrencyController::class, 'update'], [$currency->id]), 'method' => 'PUT', 'id' => 'multicurrency_add_form' ]) !!}
        {!! Form::hidden('hidden_id', $currency->id, ['id' => 'hidden_id']); !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'multicurrency::lang.edit_currency_rate' ):</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        {!! Form::label('currency_id', __('multicurrency::lang.currency') . ':') !!}
                        {!! Form::select('currency_id', $currencies, $currency->currency_id, ['class' => 'form-control select2', 'placeholder' => __('multicurrency::lang.currency'), 'required']); !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('exchange_rate', __('multicurrency::lang.exchange_rate') . ':') !!}
                        @show_tooltip(__('multicurrency::lang.tooltip_currency_exchange_factor'))
                        {!! Form::number('exchange_rate', $currency->exchange_rate, ['class' => 'form-control', 'placeholder' => __('multicurrency::lang.exchange_rate'), 'step' => '0.001']); !!}
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('type', 1, ($currency->type=='api')?1:0, ['class' => 'input-icheck', 'id' => 'type']); !!}
                                @lang('multicurrency::lang.api_exchange_rate')
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>
