
@if($conversations->count() > 0)
    @foreach($conversations as $conv)
        @php($user= $conv->sender_type == 'vendor' ? $conv->receiver :  $conv->sender)
        @if (isset($user))
            @php($unchecked=($conv->last_message?->sender_id == $user->id) ? $conv->unread_message_count : 0)
            <div
                class="chat-user-info d-flex  p-3 align-items-center customer-list view-dm-conv {{$unchecked!=0?'conv-active':''}}"
                data-url="{{route('admin.delivery-man.message-view',['conversation_id'=>$conv->id,'user_id'=>$user->id])}}" data-active-id="customer-{{$user->id}}" data-conv-id="{{ $conv->id }}" data-sender-id="{{ $user->id }}"
                id="customer-{{$user->id}}">
            <div class="chat-user-info-img d-none d-md-block">
                <img class="avatar-img onerror-image"
                     src="{{ $user['image_full_url'] ?? dynamicAsset('assets/admin/img/160x160/img1.jpg') }}"
                     data-onerror-image="{{dynamicAsset('assets/admin')}}/img/160x160/img1.jpg"
                     alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3">{{$user['f_name'].' '.$user['l_name']}}</span> <span
                        class="{{$unchecked ? 'badge badge-info' : ''}}">{{$unchecked ? $unchecked : ''}}</span>
                </h5>
                <span>{{ $user['phone'] }}</span>
                <div class="text-title line--limit-1 text-gray1 fs-12">{{ $conv->last_message?->message ? Str::limit($conv->last_message?->message ??'', 35, '...') : (count($conv->last_message->file_full_url) > 0 ?  count($conv->last_message->file_full_url) .' '. translate('messages.Attachments') :'' )}}</div>

            </div>
            </div>
{{--        @else--}}
{{--            <div class="chat-user-info d-flex  p-3 align-items-center customer-list">--}}
{{--                <div class="chat-user-info-img d-none d-md-block">--}}
{{--                    <img class="avatar-img"--}}
{{--                            src='{{dynamicAsset('assets/admin')}}/img/160x160/img1.jpg'--}}
{{--                            alt="Image Description">--}}
{{--                </div>--}}
{{--                <div class="chat-user-info-content">--}}
{{--                    <h5 class="mb-0 d-flex justify-content-between">--}}
{{--                        <span class=" mr-3">{{translate('Account_not_found')}}</span>--}}
{{--                    </h5>--}}
{{--                </div>--}}
{{--            </div>--}}
        @endif
    @endforeach
@else
    <div class="d-flex flex-column justify-content-between align-items-center h-100 py-5">
        <div class="chat-user-info text-center p-3 customer-list">
            <div class="chat-user-info- d-none d-md-block mx-auto">
                @if($type == 'admin')
                    <img class="avatar- rounded-1 mb-2 mx-auto"
                         src='{{dynamicAsset('assets/admin')}}/img/no-admin.png'
                         alt="Image Description">
                @elseif($type == 'customer')
                    <img class="avatar- rounded-1 mb-2 mx-auto"
                         src='{{dynamicAsset('assets/admin')}}/img/no-customer.png'
                         alt="Image Description">
                @else
                    <img class="avatar- rounded-1 mb-2 mx-auto"
                         src='{{dynamicAsset('assets/admin')}}/img/no-delivery.png'
                         alt="Image Description">
                @endif
            </div>
            <div class="chat-user-info-">
                <div class="mb-0">
                    @if($type == 'admin')
                        <span class="fs-14">{{translate('messages.No Admin Found')}}</span>
                    @elseif($type == 'customer')
                        <span class="fs-14">{{translate('messages.No Customer Found')}}</span>
                    @else
                        <span class="fs-14">{{translate('messages.No Deliveryman Found')}}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endif

<script>
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
</script>
