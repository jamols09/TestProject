@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{ Breadcrumbs::render('billing.payment') }}
            @if ($message = Session::pull('success'))
            <div class="custom-alerts alert alert-success ">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {!! $message !!}
            </div>
            
            @endif

            @if ($message = Session::pull('error'))
            <div class="custom-alerts alert alert-danger ">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {!! $message !!}
            </div>
            @endif
            <div class="card">

                <div class="card-header">
                    Items
                </div>

                <div class="card-body">
                    <form class="form-horizontal" method="POST" id="payment-form" role="form" action="{{ route('billing.submit') }}" >
                        {{ csrf_field() }}

                        <div class="form-group">
                            <div class="container-fluid">
                                <div class="row">
                                    @foreach ($Items as $item)
                                    <div class="col-lg-3 col-md-3 col-sm-4 col-6 text-center">
                                      
                                        <a href="/">
                                            <figure class="figure">
                                                <img src="{{asset($item->imagePath)}}" width="100" height="90" class="rounded" alt="Yoga Mattress">
                                                <figcaption class="figure-caption text-center">{{$item->name}}</figcaption>
                                                <figcaption class="figure-caption text-center">&#8369; {{$item->price}}</figcaption>
                                                <button onclick="document.getElementById('hidden_input').value = {{$item->id}}" type="submit" class="btn">Purchase</button>
                                            </figure>
                                        </a>
                                        
                                    </div>
                                    @endforeach
                                    <input type="hidden" id="hidden_input" name="item_id" value="">
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
