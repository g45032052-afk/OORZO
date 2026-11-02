@forelse ($shifts as $key => $shift)
<tr>
    <td>{{$key+$shifts->firstItem()}}</td>

    <td>
    <span class="d-block font-size-sm text-body">
        {{$shift['name']}}
    </span>
    </td>
    <td>
        {{ \App\CentralLogics\Helpers::time_format($shift->start_time) }}
    </td>
    <td>
        {{ \App\CentralLogics\Helpers::time_format($shift->end_time)  }}
    <td>
        <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox{{$shift->id}}" >
            <input class="toggle-switch-input status_change_alert" type="checkbox"  data-url="{{route('admin.shift.status',[$shift['id'],$shift->status?0:1])}}" data-message="{{ translate('Want_to_change_status_for_this_shift?') }}"
            id="stocksCheckbox{{$shift->id}}" {{$shift->status?'checked':''}}>
            <span class="toggle-switch-label">
                <span class="toggle-switch-indicator"></span>
            </span>
        </label>

        <form action="{{route('admin.shift.status',[$shift['id'],$shift->status?0:1])}}" method="get" id="stocksCheckbox-{{$shift['id']}}">
        </form>
    </td>
    <td >
        <div class="btn--container justify-content-center">

             <a href="javascript:void(0)" class="btn btn-sm btn--primary btn-outline-primary action-btn edit-shift offcanvas-trigger data-info-show"
                                            data-target="#offcanvas__customBtn3"
                                            data-id="{{ $shift->id }}"
                                            data-url="{{ route('admin.shift.edit', [$shift->id]) }}">
                                    <i class="tio-edit"></i>
                                        </a>



            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:"
            data-id="shift-{{$shift['id']}}" data-message="{{ translate('Want_to_delete_this_shift_data._All_of_data_related_to_this_shift_will_be_gone_!!!') }}" title="{{translate('messages.delete_shift')}}">
            <i class="tio-delete-outlined"></i>
            </a>
            <form action="{{route('admin.shift.delete',[$shift['id']])}}" method="post" id="shift-{{$shift['id']}}">
                @csrf @method('delete')
            </form>
        </div>
    </td>
</tr>





@empty

@endforelse
