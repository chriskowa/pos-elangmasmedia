<!-- Edit discount Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="posEditDiscountModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">
					@if($is_discount_enabled)
						@lang('sale.discount')
					@endif
					@if($is_rp_enabled)
						{{session('business.rp_name')}}
					@endif
				</h4>
			</div>
			<div class="modal-body">
				<div class="row @if(!$is_discount_enabled) hide @endif">
					<div class="col-md-12">
						<h4 class="modal-title">@lang('sale.edit_discount'):</h4>
					</div>
					<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('discount_type_modal', __('sale.discount_type') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::select('discount_type_modal', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], $discount_type , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required']); !!}
					</div>
				</div>
			</div>

			@php
				$max_discount_1 = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';
				$max_discount_2 = $max_discount_1; // Misalkan sama untuk tahap kedua
				$max_discount_3 = $max_discount_1; // Misalkan sama untuk tahap ketiga

				// Logika untuk memastikan diskon sesuai dengan batas maksimal
				if($discount_type == 'percentage') {
					if($max_discount_1 != '' && $sales_discount_1 > $max_discount_1) $sales_discount_1 = $max_discount_1;
					if($max_discount_2 != '' && $sales_discount_2 > $max_discount_2) $sales_discount_2 = $max_discount_2;
					if($max_discount_3 != '' && $sales_discount_3 > $max_discount_3) $sales_discount_3 = $max_discount_3;
				}
			@endphp

			<!-- Diskon Tahap 1 -->
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('discount_amount_modal_1', __('sale.discount_amount_1') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::text('discount_amount_modal_1', @num_format($sales_discount_1), ['class' => 'form-control input_number', 'data-max-discount' => $max_discount_1, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount_1 != '' ? @num_format($max_discount_1) : '']) ]); !!}
					</div>
				</div>
			</div>

			<!-- Diskon Tahap 2 -->
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('discount_amount_modal_2', __('sale.discount_amount_2') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::text('discount_amount_modal_2', @num_format($sales_discount_2), ['class' => 'form-control input_number', 'data-max-discount' => $max_discount_2, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount_2 != '' ? @num_format($max_discount_2) : '']) ]); !!}
					</div>
				</div>
			</div>

			<!-- Diskon Tahap 3 -->
			<div class="col-md-6">
				<div class="form-group">
					{!! Form::label('discount_amount_modal_3', __('sale.discount_amount_3') . ':*' ) !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-info"></i>
						</span>
						{!! Form::text('discount_amount_modal_3', @num_format($sales_discount_3), ['class' => 'form-control input_number', 'data-max-discount' => $max_discount_3, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount_3 != '' ? @num_format($max_discount_3) : '']) ]); !!}
					</div>
				</div>
			</div>

					<!--<div class="modal-body">
						
							<div id="discount_container">
								
							</div>
							<button type="button" class="btn btn-primary" id="add_discount_step">Tambah Diskon</button>
						
					</div>-->
				</div>
				<br>
				<div class="row @if(!$is_rp_enabled) hide @endif">
					<div class="well well-sm bg-light-gray col-md-12">
					<div class="col-md-12">
						<h4 class="modal-title">{{session('business.rp_name')}}:</h4>
					</div>
					<div class="col-md-6">
				        <div class="form-group">
				            {!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-gift"></i>
				                </span>
				                {!! Form::number('rp_redeemed_modal', $rp_redeemed, ['class' => 'form-control', 'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'), 'data-max_points' => $max_available, 'min' => 0, 'data-min_order_total' => session('business.min_order_total_for_redeem') ]); !!}
				                <input type="hidden" id="rp_name" value="{{session('business.rp_name')}}">
				            </div>
				        </div>
				    </div>
				    <div class="col-md-6">
				    	<p><strong>@lang('lang_v1.available'):</strong> <span id="available_rp">{{$max_available}}</span></p>
				    	<h5><strong>@lang('lang_v1.redeemed_amount'):</strong> <span id="rp_redeemed_amount_text">{{@num_format($rp_redeemed_amount)}}</span></h5>
				    </div>
				    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white" id="posEditDiscountModalUpdate">@lang('messages.update')</button>
			    <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.cancel')</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->