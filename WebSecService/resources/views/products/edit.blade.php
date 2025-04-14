@extends('layouts.master')
@section('title', 'Prime Numbers')
@section('content')

<form action="{{route('products_save', $product->id)}}" method="post" enctype="multipart/form-data">
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
                    @if(strpos($product->photo, 'storage/') === 0)
                        <img src="{{ asset($product->photo) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 200px;" onerror="this.onerror=null; this.src='{{ asset('images/default-product.jpg') }}';">
                    @elseif(strpos($product->photo, 'uploads/') === 0)
                        <img src="{{ asset($product->photo) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 200px;" onerror="this.onerror=null; this.src='{{ asset('images/default-product.jpg') }}';">
                    @else
                        <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 200px;" onerror="this.onerror=null; this.src='{{ asset('images/default-product.jpg') }}';">
                    @endif
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
