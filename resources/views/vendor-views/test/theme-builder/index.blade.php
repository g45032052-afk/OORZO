@extends('layouts.vendor.app')

@section('title',translate('messages.test'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <form method="POST" action="{{ route('vendor.test.menu') }}">
            @csrf
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="mb-3 text-center">
                        <button type="button" id="undoBtn" class="btn btn-danger">Undo</button>
                        <button type="button" id="redoBtn" class="btn btn-warning">Redo</button>
                    </div>
                    <nav class="navbar navbar-expand-lg navbar-light bg-light">
                        <!-- Preview -->
                        <ul class="navbar-nav" id="menuPreview">
                            <li class="nav-item active" data-id="home"><a class="nav-link">Home</a></li>
                            <li class="nav-item" data-id="category"><a class="nav-link">Category</a></li>
                            <li class="nav-item" data-id="cuisine"><a class="nav-link">Cuisine</a></li>
                            <li class="nav-item" data-id="blog"><a class="nav-link">Blog</a></li>
                            <li class="nav-item" data-id="contact"><a class="nav-link">Contact</a></li>
                        </ul>
                    </nav>
                    <div class="mt-3 text-center">
                        <h3 data-id="section-title">Browse Categories</h3>
                        <p data-id="section-des">Order essential medicines and health products delivered to your door.</p>
                    </div>
                </div>
                <div class="col-lg-4">

                    <ul class="navbar-nav d-flex flex-column" id="menuSort">
                        <li class="nav-item" data-id="home"><a class="nav-link">Home</a></li>
                        <li class="nav-item" data-id="category"><a class="nav-link">Category</a></li>
                        <li class="nav-item" data-id="cuisine"><a class="nav-link">Cuisine</a></li>
                        <li class="nav-item" data-id="blog"><a class="nav-link">Blog</a></li>
                        <li class="nav-item" data-id="contact"><a class="nav-link">Contact</a></li>
                    </ul>

                    <div class="form-group">
                        <label for="" class="form-label">Title</label>
                        <input type="text" name="" id="" data-id="section-title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="" class="form-label">Subtitle</label>
                        <textarea name="" id="" data-id="section-des" class="form-control"></textarea>
                    </div>

                    <!-- hidden input to store order -->
                    <input type="hidden" name="menu_order" id="menuOrderInput">

                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        Publish Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('script')
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script>
        "use strict";

        // $(function () {
        //     $("#menuSort").sortable({
        //         update: function () {
        //             let order = [];

        //             // Get sorted order
        //             $("#menuSort .nav-item").each(function () {
        //                 order.push($(this).data("id"));
        //             });

        //             // Rebuild preview based on order
        //             let preview = $("#menuPreview");
        //             let items = {};

        //             preview.children(".nav-item").each(function () {
        //                 let id = $(this).data("id");
        //                 items[id] = $(this);
        //             });

        //             preview.empty();

        //             order.forEach(function (id) {
        //                 if (items[id]) {
        //                     preview.append(items[id]);
        //                 }
        //             });
        //         }
        //     });

        //     $("#menuSort").disableSelection();
        // });


        // $(function () {

        //     $("#menuSort").sortable({
        //         update: function () {
        //             syncPreview();
        //         }
        //     });

        //     $("#menuSort").disableSelection();

        //     function syncPreview() {
        //         let order = [];

        //         $("#menuSort .nav-item").each(function () {
        //             order.push($(this).data("id"));
        //         });

        //         let preview = $("#menuPreview");
        //         let items = {};

        //         preview.children().each(function () {
        //             items[$(this).data("id")] = $(this);
        //         });

        //         preview.empty();

        //         order.forEach(function (id) {
        //             if (items[id]) {
        //                 preview.append(items[id]);
        //             }
        //         });

        //         preview.children().removeClass("active").first().addClass("active");
        //     }

        //     $("form").on("submit", function () {
        //         let order = [];

        //         $("#menuSort .nav-item").each(function (index) {
        //             order.push({
        //                 id: $(this).data("id"),
        //                 position: index + 1
        //             });
        //         });

        //         $("#menuOrderInput").val(JSON.stringify(order));
        //     });

        // });
    </script>
@endpush
