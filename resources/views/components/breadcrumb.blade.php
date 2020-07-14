<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        {{ $slot }}
        {{--@foreach ($breadcrumb as $key => $value)
            @if($loop->last)
                <li class="breadcrumb-item active" aria-current="page">{{$value}}</li>
            @else
                <li class="breadcrumb-item">
                    <a href="{{$key}}">{{$value}}</a>
                </li>
            @endif
        @endforeach--}}
    </ol>
</nav>