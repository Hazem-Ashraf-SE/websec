<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }
    
    /**
     * Toggle a product as favorite
     */
    public function toggle(Request $request, Product $product)
    {
        $user = Auth::user();
        
        // Check if product is already in favorites
        if ($user->favorites()->where('product_id', $product->id)->exists()) {
            // Remove from favorites
            $user->favorites()->detach($product->id);
            return response()->json([
                'status' => 'removed',
                'message' => 'Product removed from favorites'
            ]);
        } else {
            // Add to favorites
            $user->favorites()->attach($product->id);
            return response()->json([
                'status' => 'added',
                'message' => 'Product added to favorites'
            ]);
        }
    }
    
    /**
     * List all favorites for the current user
     */
    public function list(Request $request)
    {
        $user = Auth::user();
        $favorites = $user->favorites()->get();
        
        return view('products.favorites', compact('favorites'));
    }
    
    /**
     * Check if a product is in the user's favorites
     */
    public function check(Request $request, Product $product)
    {
        $user = Auth::user();
        $isFavorite = $user->favorites()->where('product_id', $product->id)->exists();
        
        return response()->json([
            'is_favorite' => $isFavorite
        ]);
    }
}
