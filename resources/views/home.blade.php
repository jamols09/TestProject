@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{ Breadcrumbs::render('home') }}
            <div class="card">
                <div class="card-header">{{ __('Project Test') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <ul>
                    <li><a href="{{ route('billing.payment') }}">Paypal Integration (v1)</a></li>
                    <li><a href="{{ route('billing.checkout') }}">Paypal Checkout (v2)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
