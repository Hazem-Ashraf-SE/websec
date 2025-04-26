@extends('layouts.master')
@section('title', 'Prime Numbers')
@section('content')

<form action="{{route('products.save', $product->id)}}" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}
    @foreach($errors->all() as $error)
    <div class="alert alert-danger">
    <strong>Error!</strong> {{$error}}
    </div>
    @endforeach
    <div class="row mb-2">
        <div class="col-6">
            <label for="code" class="form-label">Code:</label>
            <input type="text" class="form-control" placeholder="Code" name="code" required value="{{$product->code}}">
        </div>
        <div class="col-6">
            <label for="model" class="form-label">Model:</label>
            <input type="text" class="form-control" placeholder="Model" name="model" required value="{{$product->model}}">
        </div>
    </div>
    <div class="row mb-2">
        <div class="col">
            <label for="name" class="form-label">Name:</label>
            <input type="text" class="form-control" placeholder="Name" name="name" required value="{{$product->name}}">
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-6">
            <label for="model" class="form-label">Price:</label>
            <input type="numeric" class="form-control" placeholder="Price" name="price" required value="{{$product->price}}">
        </div>
        <div class="col-6">
            <label for="photo" class="form-label">Photo:</label>
            <input type="file" class="form-control" name="photo" accept="image/*">
            @if($product->photo)
                <div class="mt-2">
                    @php
                        $photoPath = $product->photo;
                        $imageUrl = '';
                        // Case 1: Storage path format
                        if (strpos($photoPath, 'storage/products/') === 0) {
                            // Remove 'storage/' from the start since we're using Laravel's storage links
                            $imageUrl = asset(str_replace('storage/', '', $photoPath));
                        }
                        // Case 2: Old format with timestamp prefix (1744481138_)
                        elseif (strpos($photoPath, 'products/1744') === 0) {
                            $imageUrl = asset('uploads/' . $photoPath);
                        }
                        // Case 3: Just products prefix
                        elseif (strpos($photoPath, 'products/') === 0) {
                            $imageUrl = asset('uploads/' . $photoPath);
                        }
                        // Case 4: Just filename or any other case
                        else {
                            $imageUrl = asset('uploads/products/' . basename($photoPath));
                        }
                    @endphp
                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 200px;" onerror="this.onerror=null; this.src='{{ asset('images/default-product.jpg') }}';">
                </div>
            @endif
        </div>
    </div>
    <div class="row mb-2">
        <div class="col">
            <label for="name" class="form-label">Description:</label>
            <textarea type="text" class="form-control" placeholder="Description" name="description" required>{{$product->description}}</textarea>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
@endsection
