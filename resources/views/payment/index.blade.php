@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{ Breadcrumbs::render('billing.payment') }}
            <div class="card">
                <div class="card-header">
                    Items
                </div>

                <div class="card-body">
                    <form class="form-horizontal" method="POST" id="payment-form" role="form" action="{{ route('billing.submit') }}" >
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="amount" class="col-md-4 control-label">Sample Item</label>
                            <div class="col-md-6">
                                <img src="{{asset('images/mat-img.jpg')}}" width=250px; height=150px; alt="Sample Img">
                            </div>
                            <div class="col-md-6">
                                <input id="amount" type="text" class="form-control" name="amount" value="586.00" readonly>

                                @if ($errors->has('amount'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('amount') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-secondary">Purchase</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
