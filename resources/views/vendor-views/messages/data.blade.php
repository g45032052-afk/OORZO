@forelse($conversations as $conv)
    @php($user= $conv->sender_type == 'vendor' ? $conv->receiver :  $conv->sender)
    @if($user)
        @php($unchecked=($conv->last_message?->sender_id == $user->id) ? $conv->unread_message_count : 0)
        <div
            class="chat-user-info d-flex  p-3 align-items-center customer-list {{$unchecked ? 'conv-active' : ''}}"
            onclick="viewConvs('{{route('vendor.message.view',['conversation_id'=>$conv->id,'user_id'=>$user->id])}}','customer-{{$user->id}}','{{ $conv->id }}','{{ $user->id }}')"
            id="customer-{{$user->id}}">
            <div class="chat-user-info-img d-none d-md-block position-relative">
                <img class="avatar-img onerror-image"
                     data-onerror-image="{{dynamicAsset('assets/admin/img/160x160/img1.jpg')}}"
                     src="{{ $user['image_full_url'] }}"
                     alt="Image Description">
                @if (($tab ?? null) == 'customer' && !empty($user->pro_status))
                    @include('partials._pro_customer_badge')
                @endif
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3">{{$user['f_name'].' '.$user['l_name']}}</span> <span
                        class="{{$unchecked ? 'badge badge-info' : ''}}">{{$unchecked ? $unchecked : ''}}</span>
                        <small>
                            {{-- {{ \App\CentralLogics\Helpers::time_format($conv->last_message?->created_at) }} --}}
                            {{ Carbon\Carbon::parse($conv->last_message?->created_at)->diffForHumans() }}
                                </small>
                </h5>
                <span>{{ $user['phone'] }}</span>
                <div class="text-title line--limit-1 text-gray1 fs-12">{{ $conv->last_message?->message ? Str::limit($conv->last_message?->message ??'', 35, '...') : (count($conv->last_message->file_full_url) > 0 ?  count($conv->last_message->file_full_url) .' '. translate('messages.Attachments') :'' )}}</div>
            </div>
        </div>
    @else
        <!-- <div
            class="chat-user-info d-flex  p-3 align-items-center customer-list">
            <div class="chat-user-info-img d-none d-md-block">
                <img class="avatar-img"
                        src='{{dynamicAsset('assets/admin')}}/img/160x160/img1.jpg'
                        alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3">{{translate('messages.user_not_found')}}</span>
                </h5>
            </div>
        </div> -->
        <div class="h-100 d-flex align-items-center justify-content-center">
            <div
                class="chat-user-info text-center p-3 customer-list">
                <div class="chat-user-info- d-none d-md-block mx-auto">
                    <img class="avatar- rounded-1 mb-2 mx-auto"
                            src='{{dynamicAsset('assets/admin')}}/img/no-admin.png'
                            alt="Image Description">
                </div>
                <div class="chat-user-info-">
                    <div class="mb-0">
                        <span class="fs-14">{{translate('messages.No Admin Found')}}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
@empty
    <div class="h-100 d-flex align-items-center justify-content-center">
        <div
            class="chat-user-info text-center p-3 customer-list">
            <div class="chat-user-info- d-none d-md-block mx-auto">
                @if($tab == 'customer')
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
                    @if($tab == 'customer')
                        <span class="fs-14">{{translate('messages.No Customer Found')}}</span>
                    @else
                        <span class="fs-14">{{translate('messages.No Deliveryman Found')}}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforelse
<script src="{{dynamicAsset('assets/admin')}}/js/view-pages/common.js"></script>
