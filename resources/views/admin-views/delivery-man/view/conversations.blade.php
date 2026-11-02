@extends('layouts.admin.app')

@section('title',translate('Delivery_Man_Preview'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <i class="tio-user"></i>
                </span>
                <span>{{$dm['f_name'].' '.$dm['l_name']}}</span>
            </h1>
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <!-- Nav -->
                <ul class="nav nav-tabs page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'info'])}}"  aria-disabled="true">{{translate('messages.info')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'transaction'])}}"  aria-disabled="true">{{translate('messages.transaction')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'timelog'])}}"  aria-disabled="true">{{translate('messages.timelog')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{route('admin.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'conversation'])}}"  aria-disabled="true">{{translate('messages.conversations')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{route('admin.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'disbursement'])}}"  aria-disabled="true">{{translate('messages.disbursements')}}</a>
                    </li>
                </ul>
                <!-- End Nav -->
            </div>
        </div>
        <!-- End Page Header -->

        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-header-title">{{ translate('messages.conversation_list') }}</h1>
            </div>
            <!-- End Page Header -->

            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <!-- Card -->
                    <div class="card">
                        <div class="card-header border-0">
                         
                            <div class="conversation-custom-search__wrap w-100 position-relative">
                                <div class="input-group rounded overflow-hidden">
                                    <input type="text" class="form-control border-inline-end-0" id="serach" placeholder="{{ translate('messages.search') }}" aria-label="Username"
                                    aria-describedby="basic-addon1" autocomplete="off">
                                    <button type="button" class="btn cursor-pointer p-0 border-0 input-group-prepend border-inline-end-0 bg--F0F2F5">
                                        <span class="input-group-text border-inline-end-0" id="basic-addon1"><i class="tio-search"></i></span>
                                    </button>
                                </div>

                            </div>

                        </div>
                        <!-- Body -->
                        <div class="card-body p-0 initial-19" id="dm-conversation-list">
                            @include('admin-views.delivery-man.partials._conversation_list')
                        </div>
                        <!-- End Body -->
                    </div>
                    <!-- End Card -->
                </div>
                <div class="col-lg-8 col-nd-6" id="dm-view-conversation">
                    <div class="text-center mt-2">
                        <h4 class="initial-20">{{ translate('messages.view_conversation') }}
                        </h4>
                    </div>
                    {{-- view here --}}
                </div>
            </div>
            <!-- End Row -->
        </div>

    </div>
@endsection

@push('script_2')
<script>
    "use strict";

     $(document).on('click', '.view-conv', function() {
        let url = $(this).data('url');
        let id_to_active = $(this).data('active-id');
        let conv_id = $(this).data('conv-id');
        let sender_id = $(this).data('sender-id');
        viewConvs(url, id_to_active, conv_id, sender_id);
    });


    function viewConvs(url, id_to_active, conv_id, sender_id) {
        $('.customer-list').removeClass('conv-active');
        $('#' + id_to_active).addClass('conv-active');
        let new_url= "{{route('admin.delivery-man.preview', ['id'=>$dm->id, 'tab'=> 'conversation'])}}" + '?conversation=' + conv_id+ '&user=' + sender_id;
            $.get({
                url: url,
                success: function(data) {
                    window.history.pushState('', 'New Page Title', new_url);
                    $('#dm-view-conversation').html(data.view);
                }
            });
    }

    let page = 1;
    let user_id =  $('#deliver_man').val();
    let isLoading = false;
    let hasMore = true;

    const container = $('#dm-conversation-list');

    container.on('scroll', function () {

        if (container.scrollTop() + container.innerHeight() >= this.scrollHeight - 50) {

            if (isLoading || !hasMore) return;

            isLoading = true;
            page++;

            loadMoreData(page).always(function () {
                isLoading = false;
            });
        }
    });


    function loadMoreData(page) {
        return $.ajax({
            url: "{{ route('admin.delivery-man.message-list-search') }}",
            type: "GET",
            data: {
                page: page,
                user_id: user_id
            }
        })
        .done(function (data) {
            if (!data.html || data.html.trim() === "") {
                hasMore = false;
                return;
            }

            $("#dm-conversation-list").append(data.html);
        })
        .fail(function () {
            alert('Server not responding...');
        });
    }

    function fetch_data(page, query) {
            $.ajax({
                url: "{{ route('admin.delivery-man.message-list-search') }}" + '?page=' + 1 + "&key=" + query,
                type: "get",
                data:{"user_id":user_id},
                success: function(data) {
                    $('#dm-conversation-list').empty();
                    $("#dm-conversation-list").append(data.html);
                }
            })
    };

    $(document).on('keyup', '#serach', function() {
        let query = $('#serach').val();
        fetch_data(page, query);
    });
</script>
@endpush
