{{-- Prompt for promotions the admin assigned this restaurant, shown on the dashboard when
     the invitation is both unanswered and unopened.

     It used to render on every vendor page and reappear on every load, which made it an
     obstacle rather than a notice: the vendor dismissed it, moved a page, and got it again.
     It is now announced the way a new order is - once, until the restaurant opens the list
     that clears it, and again only when the admin sends a fresh request.

     $pendingBogo / $pendingHappyHour are supplied by the view composer in
     AppServiceProvider, which reads that unread state, so this partial runs no queries of
     its own. --}}
@php($adminRequests = collect([
    $pendingBogo ?? null
        ? [
            'title' => translate('messages.BOGO_Campaign_Request'),
            'body' => translate('messages.The_Admin_has_invited_you_to_join_a_BOGO_Offer_Review_and_approve_the_request_to_participate'),
            'url' => route('vendor.bogo-offer.list'),
        ]
        : null,
    $pendingHappyHour ?? null
        ? [
            'title' => translate('messages.Happy_Hour_Request'),
            'body' => translate('messages.The_Admin_has_invited_you_to_join_a_Happy_Hour_Review_and_approve_the_request_to_participate'),
            'url' => route('vendor.happy-hour.list'),
        ]
        : null,
])->filter()->values())

@if($adminRequests->isNotEmpty())
    @php($request = $adminRequests->first())

    <div class="modal fade" id="adminRequestModal" tabindex="-1" role="dialog"
         data-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:480px">
            <div class="modal-content" style="border-radius:16px">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close w-30px h-30px border-0 rounded-circle d-center p-0 ml-auto"
                            style="background:var(--section-bg2)" data-dismiss="modal" aria-label="Close">&times;</button>
                </div>

                <div class="modal-body text-center px-4 pb-4 pt-0">
                    <svg width="104" height="93" viewBox="0 0 24 24" fill="none" aria-hidden="true" class="mb-4">
                        <path d="M12 3.5c-.6 0-1.15.32-1.45.84L2.7 18.16A1.67 1.67 0 0 0 4.15 20.7h15.7a1.67 1.67 0 0 0 1.45-2.54L13.45 4.34A1.67 1.67 0 0 0 12 3.5Z" fill="#DC4B44"/>
                        <path d="M12 8.75v5.1" stroke="#fff" stroke-width="2.1" stroke-linecap="round"/>
                        <circle cx="12" cy="17.35" r="1.2" fill="#fff"/>
                    </svg>

                    <h4 class="font-bold mb-3">{{ $request['title'] }}</h4>
                    <p class="opacity-75 mb-4 px-2">{{ $request['body'] }}</p>

                    <a href="{{ $request['url'] }}" class="btn btn--primary h--45px min-w-120 px-4">
                        {{ translate('messages.View_Details') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('script_2')
        <script>
            "use strict";
            // Shown on load rather than from a trigger - it is a notice, not an action. The
            // old guard against opening it on the promotion list itself is gone with the
            // layout include: this is only ever rendered on the dashboard now.
            $(function () {
                $('#adminRequestModal').modal('show');
            });
        </script>
    @endpush
@endif
