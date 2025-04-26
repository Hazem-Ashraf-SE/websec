<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller {

	use ValidatesRequests;

	public function __construct()
    {
        $this->middleware('auth:web');
    }

	public function list(Request $request) {
		if (!auth()->check()) {
			return redirect()->route('login')->with('error', 'Please login to view products.');
		}

		$query = Product::select("products.*");

		$query->when($request->keywords, 
		fn($q)=> $q->where("name", "like", "%$request->keywords%"));

		$query->when($request->min_price, 
		fn($q)=> $q->where("price", ">=", $request->min_price));
		
		$query->when($request->max_price, fn($q)=> 
		$q->where("price", "<=", $request->max_price));
		
		$query->when($request->order_by, 
		fn($q)=> $q->orderBy($request->order_by, $request->order_direction??"ASC"));

		$products = $query->get();

		return view('products.list', compact('products'));
	}

	public function buy(Request $request, Product $product)
	{
		$user = Auth::user();

		if (!$user) {
			return redirect('/login');
		}

		if (!$user->hasRole('Customer')) {
			return redirect()->back()->with('error', 'Only customers can purchase products.');
		}

		if ($product->quantity <= 0) {
			return redirect()->back()->with('error', 'This product is currently out of stock.');
		}

		if ($user->credit < $product->price) {
			// Log the insufficient credit error
			DB::table('insufficient_credit_errors')->insert([
				'user_id' => $user->id,
				'user_name' => $user->name,
				'user_email' => $user->email,
				'product_id' => $product->id,
				'product_name' => $product->name,
				'product_price' => $product->price,
				'current_credit' => $user->credit,
				'insufficient_amount' => $product->price - $user->credit,
				'error_time' => now()
			]);

			return redirect()->back()->with('error', 'Insufficient credit to purchase this product. You need $' . number_format($product->price - $user->credit, 2) . ' more.');
		}

		try {
			DB::beginTransaction();

			// Record the purchase - the trigger will handle credit deduction and quantity reduction
			DB::table('purchases')->insert([
				'user_id' => $user->id,
				'product_id' => $product->id,
				'created_at' => now(),
				'updated_at' => now()
			]);

			DB::commit();
			return redirect()->back()->with('success', 'Thank you for your purchase!');
		} catch (\Exception $e) {
			DB::rollBack();
			return redirect()->back()->with('error', 'An error occurred while processing your purchase. Please try again.');
		}
	}

	public function edit(Request $request, Product $product = null) {

		if(!auth()->user()) return redirect('/');

		$product = $product??new Product();

		return view('products.edit', compact('product'));
	}

	public function save(Request $request, Product $product = null) {
		try {
			// Validate the request data
			$validator = \Validator::make($request->all(), [
				'code' => ['required', 'string', 'max:32'],
				'name' => ['required', 'string', 'max:128'],
				'model' => ['required', 'string', 'max:256'],
				'description' => ['required', 'string', 'max:1024'],
				'price' => ['required', 'numeric'],
				'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
			]);

			if ($validator->fails()) {
				return redirect()->back()
					->withErrors($validator)
					->withInput();
			}

			$product = $product ?? new Product();
			
			// Fill non-photo fields
			$product->code = $request->code;
			$product->name = $request->name;
			$product->model = $request->model;
			$product->description = $request->description;
			$product->price = $request->price;
			$product->in_stock = $request->has('in_stock') ? 1 : 0;
			
			// Set quantity based on user role
			if (auth()->user()->hasRole('Employee')) {
				$product->quantity = $request->quantity ?? 0;
			} else {
				$product->quantity = 0;
			}

			// Handle file upload
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                
                // Delete old photo if exists
                if ($product->photo) {
                    $oldPath = public_path('uploads/' . $product->photo);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                
                // Generate timestamp and clean filename
                $timestamp = '1744481138_';
                $filename = $timestamp . $file->getClientOriginalName();
                
                // Move file to uploads directory
                $file->move(public_path('uploads/products'), $filename);
                $product->photo = 'products/' . $filename;
			}

			$product->save();
			return redirect()->route('products_list')->with('success', 'Product saved successfully!');
			
		} catch (\Exception $e) {
			\Log::error('Product save error:', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return redirect()->back()
				->with('error', 'Error saving product: ' . $e->getMessage())
				->withInput();
		}
	}

	public function delete(Request $request, Product $product) {

		if(!auth()->user()->hasPermissionTo('delete_products')) abort(401);

		$product->delete();

		return redirect()->route('products_list');
	}

	public function updateQuantity(Request $request, Product $product)
	{
		if(!auth()->user()->hasRole('Employee')) abort(401);

		$request->validate([
			'quantity' => 'required|integer|min:0'
		]);

		$product->quantity = $request->quantity;
		$product->save();

		return redirect()->back()->with('success', 'Product quantity updated successfully.');
	}

	/*
	public function __construct(){
		$this->middleware('role:Employee');  // Only allow employees to manage products
	}
	*/

	public function store(Request $request){
		$this->authorize('create', Product::class);  // Only employees can create products
		// Product creation logic
	}

	public function update(Request $request, Product $product){
		$this->authorize('update', $product);
		// Product update logic
	}

	public function destroy(Product $product){
		$this->authorize('delete', $product);
		// Product deletion logic
	}

	public function returnProduct(Request $request, $user_id, $product_id)
	{
		try {
			DB::beginTransaction();
			
			// Call the stored procedure to handle the return
			DB::select('CALL return_product(?, ?)', [$user_id, $product_id]);
			
			DB::commit();
			
			return redirect()->back()->with('success', 'Product returned successfully.');
		} catch (\Exception $e) {
			DB::rollBack();
			return redirect()->back()->with('error', 'Failed to return product. ' . $e->getMessage());
		}
	}
} 