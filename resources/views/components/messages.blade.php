@if(session()->has('status'))
    <div class="mt-3 alert alert-success" role="alert">
        <?=session('status')?>
    </div>
@endif
@if(session()->has('success'))
    <div class="mt-3 alert alert-success" role="alert">
        {{session('success')}}
    </div>
@endif
@if(session()->has('error'))
    <div class="mt-3 alert alert-danger" role="alert">
        {{session('error')}}
    </div>
@endif
@if(session()->has('warning'))
    <div class="mt-3 alert alert-warning" role="alert">
        {{session('warning')}}
    </div>
@endif