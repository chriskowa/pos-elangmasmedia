@extends('layouts.app')
@section('title', __('multicurrency::lang.manageMultiCurrency'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @lang('multicurrency::lang.manageMultiCurrency')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('multicurrency::lang.all_your_currency')])
        {{-- @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\MultiCurrency\Http\Controllers\MultiCurrencyController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('multicurrency::lang.add_currency_rate')</a>
            </div>
        @endslot --}}
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action([\Modules\MultiCurrency\Http\Controllers\MultiCurrencyController::class, 'create'])}}" 
                    data-container=".multicurrency_add_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="multicurrency_table">
                <thead>
                    <tr>
                        <th>@lang('multicurrency::lang.currency')</th>
                        <th>@lang('multicurrency::lang.exchange_rate')</th>
                        <th>@lang('multicurrency::lang.exchange_type')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade multicurrency_add_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade multicurrency_edit_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>

@endsection
<!-- /.content -->

@section('javascript')
<script type="text/javascript">
    var base_path = "{{url('/')}}";

    function toggleFieldsBasedOnCheckbox() {
        var isChecked = $('#type').is(':checked');
        if (isChecked) {
            // Disable fields or perform other actions when checked
            $('#exchange_rate').prop('disabled', true);
            $('#exchange_rate').val('');
        } else {
            // Enable fields or perform other actions when unchecked
            $('#exchange_rate').prop('disabled', false);
        }
    }

    //Multicurrenc setting CRUD
    multicurrency = $('#multicurrency_table').DataTable({
        processing: true,
        serverSide: true,
        bPaginate: false,
        buttons: [],
        ajax: base_path + '/multi_currency_settings',
        columnDefs: [
            {
                targets: 3,
                orderable: false,
                searchable: false,
            },
        ],
    });

    $('.multicurrency_add_modal, .multicurrency_edit_modal').on('shown.bs.modal', function(e) {
        toggleFieldsBasedOnCheckbox();
        $('form#multicurrency_add_form')
            .submit(function(e) {
                e.preventDefault();
            })
            .validate({
                submitHandler: function(form) {
                    e.preventDefault();
                    var data = $(form).serialize();

                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        beforeSend: function(xhr) {
                            __disable_submit_button($(form).find('button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.success == true) {
                                $('div.multicurrency_add_modal').modal('hide');
                                $('div.multicurrency_edit_modal').modal('hide');
                                toastr.success(result.msg);
                                multicurrency.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                },
            })

    });

    $(document).on('change', '#type', function() {
        toggleFieldsBasedOnCheckbox();
    });

    $(document).on('click', 'button.delete_m_currency_button', function(){
        swal({
            title: LANG.sure,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();

                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result){
                        if(result.success === true){
                            toastr.success(result.msg);
                            multicurrency.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });

</script>
@endsection