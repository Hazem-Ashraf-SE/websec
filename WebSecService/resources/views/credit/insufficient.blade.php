@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Insufficient Credit Errors</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Product</th>
                                    <th>Product Price</th>
                                    <th>Current Credit</th>
                                    <th>Insufficient Amount</th>
                                    <th>Error Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insufficientCreditErrors as $error)
                                    <tr>
                                        <td>{{ $error->user_name }} ({{ $error->user_email }})</td>
                                        <td>{{ $error->product_name }}</td>
                                        <td>${{ number_format($error->product_price, 2) }}</td>
                                        <td>${{ number_format($error->current_credit, 2) }}</td>
                                        <td>${{ number_format($error->insufficient_amount, 2) }}</td>
                                        <td>{{ $error->error_time }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 