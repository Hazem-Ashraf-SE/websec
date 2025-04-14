<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$products = DB::table('products')->get();

foreach ($products as $product) {
    echo "ID: {$product->id}, Name: {$product->name}, Photo: {$product->photo}\n";
} 