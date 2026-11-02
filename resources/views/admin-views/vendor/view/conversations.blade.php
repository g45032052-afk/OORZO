@extends('layouts.admin.app')

@section('title',$restaurant->name."'s". translate('conversation'))

@push('css_or_js')
    <!-- Custom styles for this page -->
    <link href="{{dynamicAsset('assets/admin/css/croppie.css')}}" rel="stylesheet">

@endpush

@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
                <h1 class="page-header-title text-break">
                <i class="tio-museum"></i> <span>{{$restaurant->name}} @include('partials._verified_restaurant_badge', ['restaurant' => $restaurant])</span>
            </h1>
        </div>
        <!-- Nav Scroller -->
        <div class="js-nav-scroller hs-nav-scroller-horizontal">
            <span class="hs-nav-scroller-arrow-prev initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:">
                    <i class="tio-chevron-left"></i>
                </a>
            </span>

            <span class="hs-nav-scroller-arrow-next initial-hidden">
                <a class="hs-nav-scroller-arrow-link" href="javascript:">
                    <i class="tio-chevron-right"></i>
                </a>
            </span>

            <!-- Nav -->
            @include('admin-views.vendor.view.partials._header',['restaurant'=>$restaurant])

            <!-- End Nav -->
        </div>
        <!-- End Nav Scroller -->
    </div>
        <!-- End Page Header -->
    <!-- Page Heading -->
    <div class="tab-content">
        <div class="tab-pane fade show active" id="product">
            <div class="row pt-2">
                <div class="content container-fluid">
                    <!-- Page Header -->
{{--                    <div class="page-header">--}}
{{--                        <h1 class="page-header-title">{{ translate('messages.conversation_list') }}</h1>--}}
{{--                    </div>--}}
                    <!-- End Page Header -->

                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <!-- Card -->
                            <div class="card">
                                <div class="card-header border-0">
                                    {{--<div class="input-group input---group">
                                        <div class="input-group-prepend border-inline-end-0">
                                            <span class="input-group-text border-inline-end-0" id="basic-addon1"><i class="tio-search"></i></span>
                                        </div>
                                        <input type="text" class="form-control border-inline-start-0 pl-1" id="serach" placeholder="{{ translate('messages.search') }}" aria-label="Username"
                                            aria-describedby="basic-addon1" autocomplete="off">
                                    </div>--}}
                                    <div class="conversation-custom-search__wrap w-100 position-relative">
                                        <div class="input-group rounded overflow-hidden">
                                            <input type="text" class="form-control border-inline-end-0" id="serach" placeholder="{{ translate('messages.search') }}" aria-label="Username"
                                            aria-describedby="basic-addon1" autocomplete="off">
                                            <button type="button" class="btn cursor-pointer p-0 border-0 input-group-prepend border-inline-end-0 bg--F0F2F5">
                                                <span class="input-group-text border-inline-end-0" id="basic-addon1"><i class="tio-search"></i></span>
                                            </button>
                                        </div>
{{--                                        <div class="chat-user-info__search">--}}
{{--                                            <div class="d-flex flex-column gap-3">--}}
{{--                                                <div class="text-title fs-14 text-capitalize">--}}
{{--                                                    Mr. Deniel--}}
{{--                                                </div>--}}
{{--                                                <div class="text-title fs-14 text-capitalize">--}}
{{--                                                    Dallas--}}
{{--                                                </div>--}}
{{--                                                <div class="text-title fs-14 text-capitalize">--}}
{{--                                                Dahlia--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                    </div>
                                </div>
                                <input type="hidden" id="vendor_id" value="{{ $restaurant->id }}">
                                <!-- Body -->
                                <div class="card-body p-0 initial-55">
                                    <div class="border-bottom"></div>
                                    <ul class="nav nav-tabs mb-3 border-0 conversation-update">
                                        <li class="nav-item">
                                            <a href="{{route('admin.restaurant.view', [$restaurant->id,'conversations','type'=> 'customer'])}}" class="nav-link {{$type=='customer'?'active':''}}">{{ translate('Customer') }}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{route('admin.restaurant.view', [$restaurant->id,'conversations','type'=> 'delivery_man'])}}" class="nav-link {{$type=='delivery_man'?'active':''}}">{{ translate('Delivery_Man') }}</a>
                                        </li>
{{--                                        <li class="nav-item">--}}
{{--                                            <a href="{{route('admin.restaurant.view', [$restaurant->id,'conversations','type'=> 'admin'])}}" class="nav-link {{$type=='admin'?'active':''}}">{{ translate('Admin') }}</a>--}}
{{--                                        </li>--}}
                                    </ul>
                                    <div id="vendor-conversation-list">
                                        @include('admin-views.vendor.view.partials._conversation_list')
                                    </div>

                                </div>
                                <!-- End Body -->
                            </div>
                            <!-- End Card -->
                        </div>
                        <div class="col-lg-8 col-nd-6" id="vendor-view-conversation">
                            <div class="card h-100">
                                <div class="h-100 d-flex align-items-center justify-content-center">
                                    <div class="text-center mt-3">
                                        <img width="46" height="46" src="{{ dynamicAsset('assets/admin/img/no-conversation.png') }}" alt="img" class="mb-2 opacity-75">
                                        <p class="color-8a8a8a">{{ translate('messages.You haven’t any conversation yet') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Row -->
                </div>


            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
    "use strict";
    $('.view-dm-conv').on('click', function (){
        let url = $(this).data('url');
        let id_to_active = $(this).data('active-id');
        let conv_id = $(this).data('conv-id');
        let sender_id = $(this).data('sender-id');
        viewConvs(url, id_to_active, conv_id, sender_id);
    })

    function viewConvs(url, id_to_active, conv_id, sender_id) {
        $('.customer-list').removeClass('conv-active');
        $('#' + id_to_active).addClass('conv-active');
        let new_url= "{{route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'conversations'])}}" + '?conversation=' + conv_id+ '&user=' + sender_id;
            $.get({
                url: url,
                success: function(data) {
                    window.history.pushState('', 'New Page Title', new_url);
                    $('#vendor-view-conversation').html(data.view);
                }
            });
    }

    let page = 1;
    let user_id =  $('#vendor_id').val();
    $('#vendor-conversation-list').scroll(function() {
        if ($('#vendor-conversation-list').scrollTop() + $('#vendor-conversation-list').height() >= $('#vendor-conversation-list')
            .height()) {
            page++;
            loadMoreData(page);
        }
    });

    function loadMoreData(page) {
        $.ajax({
                url: "{{ route('admin.restaurant.message-list') }}" + '?page=' + page,
                type: "get",
                data:{"user_id":user_id},
                beforeSend: function() {

                }
            })
            .done(function(data) {
                if (data.html == " ") {
                    return;
                }
                $("#vendor-conversation-list").append(data.html);
            })
            .fail(function(jqXHR, ajaxOptions, thrownError) {
                alert('server not responding...');
            });
    };

    function fetch_data(page = 1, query = '', type = '') {
        $.ajax({
            url: "{{ route('admin.restaurant.message-list') }}" + '?page=' + page + "&key=" + query + '&type=' + type,
            type: "GET",
            data: { "user_id": user_id },
            success: function(data) {
                $('#vendor-conversation-list').empty().append(data.html);
            }
        });
    }

    $(document).on('keyup', '#serach', function() {
        let query = $(this).val();
        let page = 1;
        let type = "{{ request()->type }}";
        fetch_data(page, query, type);
    });

</script>
@endpush
