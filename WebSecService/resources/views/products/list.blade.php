@extends('layouts.master')
@section('title', 'Test Page')
@section('content')
<div class="row mt-2">
    <div class="col col-10">
        <h1>Products</h1>
    </div>
    <div class="col col-2">
        @can('add_products')
        <a href="{{route('products_edit')}}" class="btn btn-success form-control">Add Product</a>
        @endcan
    </div>
</div>

@if(auth()->user()->hasRole('Customer'))
    <div class="alert alert-info">
        Your current credit: ${{ number_format(auth()->user()->credit, 2) }}
    </div>
@endif

<form>
    <div class="row">
        <div class="col col-sm-2">
            <input name="keywords" type="text"  class="form-control" placeholder="Search Keywords" value="{{ request()->keywords }}" />
        </div>
        <div class="col col-sm-2">
            <input name="min_price" type="numeric"  class="form-control" placeholder="Min Price" value="{{ request()->min_price }}"/>
        </div>
        <div class="col col-sm-2">
            <input name="max_price" type="numeric"  class="form-control" placeholder="Max Price" value="{{ request()->max_price }}"/>
        </div>
        <div class="col col-sm-2">
            <select name="order_by" class="form-select">
                <option value="" {{ request()->order_by==""?"selected":"" }} disabled>Order By</option>
                <option value="name" {{ request()->order_by=="name"?"selected":"" }}>Name</option>
                <option value="price" {{ request()->order_by=="price"?"selected":"" }}>Price</option>
            </select>
        </div>
        <div class="col col-sm-2">
            <select name="order_direction" class="form-select">
                <option value="" {{ request()->order_direction==""?"selected":"" }} disabled>Order Direction</option>
                <option value="ASC" {{ request()->order_direction=="ASC"?"selected":"" }}>ASC</option>
                <option value="DESC" {{ request()->order_direction=="DESC"?"selected":"" }}>DESC</option>
            </select>
        </div>
        <div class="col col-sm-1">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
        <div class="col col-sm-1">
            <button type="reset" class="btn btn-danger">Reset</button>
        </div>
    </div>
</form>

@foreach($products as $product)
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
                        <!-- Debug info: {{ $product->name }} - {{ $photoPath }} -->
                        <!-- Debug info: {{ $product->name }} - {{ $photoPath }} - {{ $imageUrl }} -->
                    @else
                        <img src="{{ asset('images/default-product.jpg') }}" class="img-thumbnail" alt="{{$product->name}}" width="100%">
                    @endif
                </div>
                <div class="col col-sm-12 col-lg-8 mt-3">
                    <div class="row mb-2">
                        <div class="col-6">
                            <h3>{{$product->name}}</h3>
                        </div>
                        @if(auth()->user()->hasRole('Customer'))
                        <div class="col col-2">
                            <button class="btn form-control favorite-btn" data-product-id="{{ $product->id }}">
                                <i class="fas fa-heart favorite-icon" id="favorite-icon-{{ $product->id }}"></i> <span id="favorite-text-{{ $product->id }}">Favorite</span>
                            </button>
                        </div>
                        @endif
                        <div class="col col-2">
                            @can('edit_products')
                                <a href="{{route('products_edit', $product->id)}}" class="btn btn-success form-control">Edit</a>
                            @endcan
                        </div>
                        <div class="col col-2">
                            @can('delete_products')
                                <a href="{{route('products_delete', $product->id)}}" class="btn btn-danger form-control">Delete</a>
                            @endcan
                        </div>
                    </div>

                    <table class="table table-striped">
                        <tr><th width="20%">Name</th><td>{{$product->name}}</td></tr>
                        <tr><th>Model</th><td>{{$product->model}}</td></tr>
                        <tr><th>Code</th><td>{{$product->code}}</td></tr>
                        <tr><th>Price</th><td>${{ number_format($product->price, 2) }}</td></tr>
                        @if(auth()->user()->hasRole('Employee'))
                            <tr>
                                <th>Quantity</th>
                                <td>
                                    <form action="{{ route('products.update_quantity', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group" style="max-width: 200px;">
                                            <input type="number" name="quantity" value="{{ $product->quantity }}" class="form-control" min="0">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @elseif(auth()->user()->hasRole(['Admin', 'Customer']))
                            <tr>
                                <th>Quantity</th>
                                <td>{{ $product->quantity }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>In Stock</th>
                            <td>
                                @if ($product->quantity > 0)
                                    @if(auth()->user()->hasRole('Customer'))
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
                                        <span class="badge bg-success">In Stock</span>
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
        // Add Font Awesome CSS if not already included
        if (!$('link[href*="font-awesome"]').length) {
            $('head').append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">');
        }
        
        // Style for favorite buttons
        $('.favorite-btn').each(function() {
            const productId = $(this).data('product-id');
            
            // Check if product is in favorites
            $.get(`{{ url('favorites/check') }}/${productId}`, function(response) {
                if (response.is_favorite) {
                    $(`#favorite-icon-${productId}`).addClass('text-danger');
                    $(`#favorite-text-${productId}`).text('Remove');
                    $(`.favorite-btn[data-product-id="${productId}"]`).addClass('btn-outline-danger');
                } else {
                    $(`#favorite-icon-${productId}`).addClass('text-secondary');
                    $(`#favorite-text-${productId}`).text('Add to Favorites');
                    $(`.favorite-btn[data-product-id="${productId}"]`).addClass('btn-outline-secondary');
                }
            });
        });
        
        // Set up CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        // Handle favorite button click
        $('.favorite-btn').on('click', function() {
            const productId = $(this).data('product-id');
            const button = $(this);
            const icon = $(`#favorite-icon-${productId}`);
            const text = $(`#favorite-text-${productId}`);
            
            $.ajax({
                url: `{{ url('favorites/toggle') }}/${productId}`,
                type: 'POST',
                success: function(response) {
                    if (response.status === 'added') {
                        icon.removeClass('text-secondary').addClass('text-danger');
                        text.text('Remove');
                        button.removeClass('btn-outline-secondary').addClass('btn-outline-danger');
                    } else {
                        icon.removeClass('text-danger').addClass('text-secondary');
                        text.text('Add to Favorites');
                        button.removeClass('btn-outline-danger').addClass('btn-outline-secondary');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr, status, error);
                    alert('Error updating favorites. Please try again.');
                }
            });
        });
    });
</script>
@endsection