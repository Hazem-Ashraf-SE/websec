@extends('layouts.master')
@section('title', 'My Favorite Products')
@section('content')
<div class="row mt-2">
    <div class="col col-10">
        <h1>My Favorite Products</h1>
    </div>
    <div class="col col-2">
        <a href="{{ route('products_list') }}" class="btn btn-primary form-control">Back to Products</a>
    </div>
</div>

@if(auth()->user()->hasRole('Customer'))
    <div class="alert alert-info">
        Your current credit: ${{ number_format(auth()->user()->credit, 2) }}
    </div>
@endif

@if($favorites->isEmpty())
    <div class="alert alert-warning mt-3">
        You don't have any favorite products yet. Browse the <a href="{{ route('products_list') }}">products page</a> to add some!
    </div>
@endif

@foreach($favorites as $product)
    <div class="card mt-2">
        <div class="card-body">
            <div class="row">
                <div class="col col-sm-12 col-lg-4">
                    @if($product->photo)
                        @php
                            $photoPath = $product->photo;
                            $imageUrl = asset('uploads/' . $photoPath);
                        @endphp
                        <img src="{{ $imageUrl }}" class="img-thumbnail" alt="{{$product->name}}" width="100%" onerror="this.onerror=null; this.src='{{ asset('images/default-product.jpg') }}';">
                    @else
                        <img src="{{ asset('images/default-product.jpg') }}" class="img-thumbnail" alt="{{$product->name}}" width="100%">
                    @endif
                </div>
                <div class="col col-sm-12 col-lg-8 mt-3">
                    <div class="row mb-2">
                        <div class="col-8">
                            <h3>{{$product->name}}</h3>
                        </div>
                        <div class="col col-4">
                            <button class="btn btn-danger form-control remove-favorite" data-product-id="{{ $product->id }}">
                                <i class="fas fa-heart-broken"></i> Remove from Favorites
                            </button>
                        </div>
                    </div>

                    <table class="table table-striped">
                        <tr><th width="20%">Name</th><td>{{$product->name}}</td></tr>
                        <tr><th>Model</th><td>{{$product->model}}</td></tr>
                        <tr><th>Code</th><td>{{$product->code}}</td></tr>
                        <tr><th>Price</th><td>${{ number_format($product->price, 2) }}</td></tr>
                        <tr>
                            <th>Quantity</th>
                            <td>{{ $product->quantity }}</td>
                        </tr>
                        <tr>
                            <th>In Stock</th>
                            <td>
                                @if ($product->quantity > 0)
                                    @if(auth()->user()->credit >= $product->price)
                                        <form action="{{ route('products.buy', $product->id) }}" method="POST" style="margin-top: 5px;">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Buy Now</button>
                                        </form>
                                    @else
                                        <button class="btn btn-danger" disabled title="Insufficient credit. You need ${{ number_format($product->price - auth()->user()->credit, 2) }} more.">
                                            Insufficient Credit
                                        </button>
                                    @endif
                                @else
                                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                                @endif
                            </td>
                        </tr>
                        <tr><th>Description</th><td>{{$product->description}}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        // Handle remove from favorites
        $('.remove-favorite').on('click', function() {
            const productId = $(this).data('product-id');
            const card = $(this).closest('.card');
            
            $.ajax({
                url: `{{ url('favorites/toggle') }}/${productId}`,
                type: 'POST',
                success: function(response) {
                    if (response.status === 'removed') {
                        card.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if there are no more favorites
                            if ($('.card').length === 0) {
                                const noFavoritesMessage = `
                                    <div class="alert alert-warning mt-3">
                                        You don't have any favorite products yet. Browse the <a href="{{ route('products_list') }}">products page</a> to add some!
                                    </div>
                                `;
                                $('.row:first').after(noFavoritesMessage);
                            }
                        });
                    }
                },
                error: function() {
                    alert('Error removing product from favorites. Please try again.');
                }
            });
        });
    });
</script>
@endsection
