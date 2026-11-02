@extends('layouts.admin.app')

@section('title', translate('Messages'))

@section('content')

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title">{{ translate('messages.conversation_list') }}</h1>
        </div>
        <!-- End Page Header -->

        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <!-- Card -->
                <div class="card h-100">
                    <div class="card-header border-0">

                        <div class="conversation-custom-search__wrap w-100 position-relative">
                            <div class="input-group rounded overflow-hidden">
                                <input type="search" class="form-control border-inline-end-0" id="serach" placeholder="{{ translate('Search_by_name_or_phone') }}" aria-label="Username" aria-describedby="basic-addon1" autocomplete="off">
                                <button type="button" class="btn cursor-pointer p-0 border-0 input-group-prepend border-inline-end-0 bg--F0F2F5">
                                    <span class="input-group-text border-inline-end-0" id="basic-addon1"><i class="tio-search"></i></span>
                                </button>
                            </div>

                        </div>
                    </div>
                    <div class="px-4">
                        <ul class="nav nav-tabs mb-3 border-0 conversation-update">
                            <li class="nav-item">
                                <a href="{{route('admin.message.list', ['tab'=> 'customer'])}}" class="nav-link {{$tab=='customer'?'active':''}}">{{ translate('Customer') }}</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{route('admin.message.list', ['tab'=> 'vendor'])}}" class="nav-link {{$tab=='vendor'?'active':''}}">{{ translate('Restaurant') }}</a>
                            </li>

                        </ul>
                    </div>

                    <!-- Body -->
                    <div class="card-body p-0 initial-19" id="conversation-list">
                        @include('admin-views.messages.data')

                    </div>
                    <!-- End Body -->

                </div>
                <!-- End Card -->
            </div>
            <div class="col-lg-8 col-md-6">
                <div id="admin-view-conversation" class="h-100 card">

                    <div class="h-100 d-flex align-items-center justify-content-center">
                        <div class="text-center mt-3">
                            <img width="46" height="46" src="{{ dynamicAsset('assets/admin/img/no-conversation.png') }}" alt="img" class="mb-2 opacity-75">
                            <p class="color-8a8a8a">{{ translate('messages.Please select a user to view the conversation.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Row -->
    </div>
    @endsection

    @push('script_2')

    <script>
        "use strict";
    function viewAdminConvs(url, id_to_active, conv_id, sender_id) {
        var tab = getUrlParameter('tab');
        $('.customer-list').removeClass('conv-active');
            $('#' + id_to_active).addClass('conv-active');
            let new_url= "{{ route('admin.message.list') }}" + '?tab=' + tab+ '&conversation=' + conv_id+ '&user=' + sender_id;
            $.get({
                url: url,
                success: function(data) {
                    window.history.pushState('', 'New Page Title', new_url);
                    $('#admin-view-conversation').html(data.view);
                    conversationList();

                }
            });

    }

        let page = 1;
        $('#conversation-list').scroll(function() {
            if ($('#conversation-list').scrollTop() + $('#conversation-list').height() >= $('#conversation-list')
                .height()) {
                page++;
                loadMoreData(page);
            }
        });

        function loadMoreData(page) {
            var tab = getUrlParameter('tab');
            $.ajax({
                    url: "{{ route('admin.message.list') }}" + '?tab=' + tab+ '&page=' + page,
                    type: "get",
                    beforeSend: function() {

                    }
                })
                .done(function(data) {
                    if (data.html == " ") {
                        return;
                    }
                    $("#conversation-list").append(data.html);
                })
                .fail(function(jqXHR, ajaxOptions, thrownError) {
                    alert('server not responding...');
                });
        }

        function fetch_data(page, query) {
            var tab = getUrlParameter('tab');
            $.ajax({
                url: "{{ route('admin.message.list') }}"+ '?tab=' + tab + '&page=' + page + "&key=" + query,
                success: function(data) {
                    $('#conversation-list').empty();
                    $("#conversation-list").append(data.html);
                }
            })
        }

        $(document).on('keyup', '#serach', function() {
            let query = $('#serach').val();
            fetch_data(page, query);
        });

        document.getElementById('serach').addEventListener('input', function(event) {
            if (this.value === "") {
                let query = $('#serach').val();
                fetch_data(page, query);
            }
        });
        @if (isset($userInfo) && request()->customer_id)
            $(document).ready(function () {
                $('#customer-{{ $userInfo->id}}').trigger('click');
            });
         @endif
    </script>
@endpush
