@extends('layouts.admin.app')

@section('title',translate('Create_BOGO_Offer'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">{{translate('messages.BOGO_Offer')}}</h1>
        </div>

        <form action="{{route('admin.bogo-offer.store')}}" method="post" enctype="multipart/form-data" id="bogo-offer-form">
            @csrf
            @include('admin-views.bogo-offer.partials._form_body', ['offer' => null])

            <div class="btn--container justify-content-end my-4">
                <button type="reset" class="btn min-w-120 btn--reset">{{translate('messages.reset')}}</button>
                <button type="submit" class="btn min-w-120 btn--primary">{{translate('messages.save')}}</button>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script>
        const formUrl = '{{ route('admin.bogo-offer.store') }}';
        const redirectUrl = '{{ route('admin.bogo-offer.list') }}';
        const successMessage = '{{ translate('messages.BOGO_offer_created_successfully') }}';
    </script>
    @include('admin-views.bogo-offer.partials._form_scripts')
@endpush
