<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Product extends Model  {

	protected $fillable = [
        'code',
        'name',
        'price',
        'model',
        'description',
        'photo',
        'in_stock',
        'quantity'
    ];

    public function purchases()
    {
        return $this->belongsToMany(User::class, 'purchases', 'product_id', 'user_id');
    }
}