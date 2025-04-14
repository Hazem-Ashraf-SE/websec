@extends('layouts.master')
@section('title', 'User Profile')
@section('content')
<div class="row">
    <div class="m-4 col-sm-6">
        <table class="table table-striped">
            <tr>
                <th>Name</th><td>{{$user->name}}</td>
            </tr>
            <tr>
                <th>Email</th><td>{{$user->email}}</td>
            </tr>
            <tr>
                <th>Roles</th>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{$role->name}}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>Permissions</th>
                <td>
                    @php
                        $shownPermissions = [];
                    @endphp
                    @foreach($permissions as $permission)
                        @php
                            $permName = strtolower($permission->name);
                            if (!in_array($permName, $shownPermissions)) {
                                $shownPermissions[] = $permName;
                        @endphp
                            <span class="badge bg-success">{{$permission->display_name ?? $permission->name}}</span>
                        @php
                            }
                        @endphp
                    @endforeach
                </td>
            </tr>
        </table>

        <div class="row">
            <div class="col col-6">
                <div>
                    <p>Your Credit: {{ auth()->user()->credit ?? 0 }}</p>
                </div>
            </div>
            @if(auth()->user()->hasPermissionTo('admin_users')||auth()->id()==$user->id)
            <div class="col col-4">
                <a class="btn btn-primary" href='{{route('edit_password', $user->id)}}'>Change Password</a>
            </div>
            @else
            <div class="col col-4">
            </div>
            @endif
            @if(auth()->user()->hasPermissionTo('edit_users')||auth()->id()==$user->id)
            <div class="col col-2">
                <a href="{{route('users_edit', $user->id)}}" class="btn btn-success form-control">Edit</a>
            </div>
            @endif
        </div>

        @if(auth()->id()==$user->id)
        <div class="row mt-3">
            <div class="col-12">
                <form action="{{ route('users_delete', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>
        </div>
        @endif

        <!-- Purchased Products Section -->
        <div class="row mt-4">
            <div class="col-12">
                <h3>Purchased Products</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Model</th>
                                <th>Price</th>
                                <th>Purchase Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchasedProducts as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->model }}</td>
                                    <td>{{ $product->price }}</td>
                                    <td>{{ $product->purchase_date }}</td>
                                    <td>
                                        <form action="{{ route('return_product', ['user_id' => auth()->id(), 'product_id' => $product->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to return this product?');">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm">Return</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No products purchased yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
