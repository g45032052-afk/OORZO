@extends('layouts.vendor.app')

@section('title',translate('messages.restaurant_wallet'))

@push('css_or_js')

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
     <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h2 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{dynamicAsset('assets/admin/img/image_90.png')}}" alt="public">
                        </div>
                        <span>
                            {{translate('messages.restaurant_wallet')}}
                        </span>
                    </h2>
                </div>
            </div>
        </div>
<!-- End Page Header -->
<?php
$wallet = \App\Models\RestaurantWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
if(isset($wallet)==false){
    \Illuminate\Support\Facades\DB::table('restaurant_wallets')->insert([
        'vendor_id'=>\App\CentralLogics\Helpers::get_vendor_id(),
        'created_at'=>now(),
        'updated_at'=>now()
    ]);
    $wallet = \App\Models\RestaurantWallet::where('vendor_id',\App\CentralLogics\Helpers::get_vendor_id())->first();
}
?>
@include('vendor-views.wallet.partials._balance_data',['wallet'=>$wallet])

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="datatable"
                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true,
                                    "paging":false
                                }' >
                            <thead class="thead-light">
                            <tr>
                                <th>{{ translate('messages.sl') }}</th>
                                <th>{{translate('messages.amount')}}</th>
                                <th>{{translate('messages.request_time')}}</th>
                                <th>{{translate('messages.Withdraw_method')}}</th>
                                <th>{{translate('messages.Transaction_Type')}}</th>
                                <th>{{translate('messages.status')}}</th>
                                <th >{{translate('messages.note')}}</th>
                                <th class="w-5px">{{ translate('messages.Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($withdraw_req as $k=>$wr)

                                <tr>
                                    <td scope="row">{{$k+$withdraw_req->firstItem()}}</td>
                                    <td> {{ \App\CentralLogics\Helpers::format_currency($wr['amount'])}}</td>

                                    <td>
                                        <span class="d-block">{{ \App\CentralLogics\Helpers::time_date_format($wr['created_at'])}}</span>
                                    </td>
                                    <td>
                                        @if($wr->method)

                                        <a href="#" data-toggle="modal" data-target="#exampleModal1-{{ $wr->id }}">
                                            {{translate($wr->method->method_name)}}
                                        </a>
                                        @elseif($wr->withdrawal_method_id)
                                            <a href="#" data-toggle="modal" data-target="#exampleModal1-{{ $wr->id }}">
                                                {{translate(\App\Models\DisbursementWithdrawalMethod::find($wr->withdrawal_method_id)->method_name)}}
                                            </a>
                                        @else
                                            {{ translate('Default_method') }}
                                        @endif

                                        <div class="modal fade" id="exampleModal1-{{ $wr->id }}" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel"        aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Withdraw_method_details')}}  </h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>

                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            @foreach(json_decode($wr->withdrawal_method_fields, true) ?? [] as $key=>$method_field)
                                                                <label class="mt-2"  for="{{$key}}">{{ translate($key)}}</label>
                                                                <input type="text" class="form-control" readonly value="{{ $method_field }}" id="{{$key}}">
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($wr->type ==  'adjustment' )
                                        {{ translate('Wallet_Adjustment') }}
                                        @elseif ($wr->type == 'manual' )
                                        {{ translate('Withdraw_Request') }}
                                        @elseif ($wr->type == 'disbursement' )
                                        {{ translate('disbursement') }}
                                        @else
                                        {{ translate($wr->type) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($wr->approved==0)
                                            <label class="badge badge-soft-info">{{translate('messages.pending')}}</label>
                                        @elseif($wr->approved==1)
                                            <label class="badge badge-soft-success">{{translate('messages.approved')}}</label>
                                        @else
                                            <label class="badge badge-soft-danger">{{translate('messages.denied')}}</label>
                                        @endif
                                    </td>

                                    <td >
                                    @if($wr->transaction_note )
                                         @if($wr->transaction_note == 'Restaurant_wallet_adjustment_partial' )
                                         {{ translate('Adjusted_Amount_Partially') }}
                                             @elseif($wr->transaction_note == 'Restaurant_wallet_adjustment_full' )
                                             {{ translate('Adjusted_Amount') }}
                                            @else
                                            {{ translate($wr->transaction_note) }}
                                          @endif
                                        @else
                                        {{ translate('messages.N/A') }}
                                      @endif
                                     </td>
                                    <td>

                                        @if($wr->approved==0)
                                            <a class="btn btn-outline-danger btn--danger action-btn form-alert"
                                               data-id="withdraw-{{$wr['id']}}"
                                               data-message="{{ translate('Want_to_delete_this_?') }}"
                                               href="javascript:"
                                               title="{{ translate('messages.Remove Withdraw Request') }}"
                                               data-bs-toggle="tooltip">
                                                <i class="tio-delete-outlined"></i>
                                            </a>


                                            <form action="{{route('vendor.wallet.close-request',[$wr['id']])}}"
                                                    method="post" id="withdraw-{{$wr['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        @else
                                            <label>{{translate('messages.complete')}}</label>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($withdraw_req) === 0)
                        <div class="empty--data">
                            <img src="{{dynamicAsset('assets/admin/img/empty.png')}}" alt="public">
                            <h5>
                                {{translate('no_data_found')}}
                            </h5>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer pt-0 border-0">
                    {{$withdraw_req->links()}}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="payment_model" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Pay_Via_Online')}}  </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>

        </div>
        <form action="{{ route('vendor.wallet.make_payment') }}" method="POST" class="needs-validation">
        <div class="modal-body">
                @csrf
                <input type="hidden" value="{{ \App\CentralLogics\Helpers::get_restaurant_id() }}" name="restaurant_id"/>
                <input type="hidden" value="{{ abs($wallet->collected_cash) }}" name="amount"/>
                <h5 class="mb-5 ">{{ translate('Pay_Via_Online') }} &nbsp; <small>({{ translate('Faster_&_secure_way_to_pay_bill') }})</small></h5>
                <div class="row g-3">
                    @forelse ($data as $item)
                        <div class="col-sm-6">
                            <div class="d-flex gap-3 align-items-center">
                                <input type="radio" required id="{{$item['gateway'] }}" name="payment_gateway" value="{{$item['gateway'] }}">
                                <label for="{{$item['gateway'] }}" class="d-flex align-items-center gap-3 mb-0">
                                    <img height="24" src="{{ dynamicStorage('storage/app/public/payment_modules/gateway_image/'. $item['gateway_image']) }}" alt="">
                                    {{ $item['gateway_title'] }}
                                </label>
                            </div>
                        </div>
                    @empty
                    <h1>{{ translate('no_payment_gateway_found') }}</h1>
                    @endforelse
                </div>
        </div>

        <div class="modal-footer">
            <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
            <button type="submit" class="btn btn-primary">{{ translate('Proceed') }}</button>
        </div>
        </form>
    </div>
    </div>
</div>


<div class="modal fade" id="Adjust_wallet" tabindex="-1"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{translate('messages.Adjust_Wallet')}}  </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>

        </div>
        <form action="{{ route('vendor.wallet.make_wallet_adjustment') }}" method="POST" class="needs-validation">
        <div class="modal-body">
                @csrf
                <h5 class="mb-5 ">{{ translate('This_will_adjust_the_collected_cash_on_your_earning') }} </h5>
        </div>

        <div class="modal-footer">
            <button id="reset_btn" type="reset" data-dismiss="modal" class="btn btn-secondary" >{{ translate('Close') }} </button>
            <button type="submit" class="btn btn-primary">{{ translate('Proceed') }}</button>
        </div>
        </form>
    </div>
    </div>
</div>
@endsection
@push('script')
<script>
    "use strict";

    let methodFieldsData = @json(
        $vendor_withdrawal_methods->mapWithKeys(function ($item) {
            return [$item->id => json_decode($item->method_fields, true)];
        })
    );

    let otherMethodIds = @json($withdrawal_methods->pluck('id')->toArray());

    $(document).on('change', '#withdraw_method', function () {
        let selectedId = $(this).val();
        let containerMy = $('#method-fields-container');
        let containerOther = $('#method-fields-div');

        // Clear both containers
        containerMy.html('');
        containerOther.html('');

        // CASE 1: "My Method" — render from local JS data
        if (methodFieldsData[selectedId]) {
            $.each(methodFieldsData[selectedId], function (key, value) {
                let label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

                let input = `
                    <div class="form-group">
                        <label class="form-label">${label}</label>
                        <input type="text" name="${key}" class="form-control" value="${value ?? ''}">
                    </div>
                `;
                containerMy.append(input);
            });
        }

        // CASE 2: "Others" — fetch method fields via AJAX
        else if (otherMethodIds.includes(parseInt(selectedId))) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('vendor.wallet.method-list') }}?method_id=" + selectedId,
                type: 'get',
                success: function (response) {
                    let method_fields = response.content.method_fields;

                    if (!method_fields || method_fields.length === 0) {
                        containerOther.html('<p>No fields available for this method.</p>');
                        return;
                    }

                    containerOther.html(''); // Clear previous

                    method_fields.forEach(field => {
                        let label = field.input_name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                        let inputType = (field.input_type === 'phone') ? 'number' : field.input_type;

                        containerOther.append(`
                            <div class="form-group mt-2">
                                <label class="fz-16 text-capitalize c1 mb-2">${label}</label>
                                <input type="${inputType}"
                                       class="form-control"
                                       name="${field.input_name}"
                                       placeholder="${field.placeholder}"
                                       ${field.is_required === 1 ? 'required' : ''}>
                            </div>
                        `);
                    });
                },
                error: function () {
                    alert('Failed to load method fields.');
                }
            });
        }
    });

    $('.payment-warning').on('click',function (event ){
            event.preventDefault();
            toastr.info(
                "{{ translate('messages.Currently,_there_are_no_payment_options_available._Please_contact_admin_regarding_any_payment_process_or_queries.') }}", {
                    CloseButton: true,
                    ProgressBar: true
                });
        });

    function showMyModal(data) {
        $(".modal-body #hiddenValue").html(data);
        $('#exampleModal').modal('show');
    }


    $('.withdrawal-methods-disable').on('click', function (){
    toastr.info( $(this).data('message') , {
        CloseButton: true,
        ProgressBar: true
    });
})

$(document).ready(function() {
    $("#withdraw_form").on("submit", function(event) {
        $('#set_disable').attr('disabled', true);

    });
});

    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
