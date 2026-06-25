{{--
    Flash messages → Toast notifications
    Triggers $store.toast.add() via Alpine x-init lifecycle.
    No visible DOM output.
--}}
@foreach (['success', 'error', 'warning', 'info'] as $type)
    @if(session($type))
    <div x-data x-init="$store.toast.add('{{ $type }}', null, {{ Js::from(session($type)) }})"
         aria-hidden="true" style="display:none"></div>
    @endif
@endforeach
