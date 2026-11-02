<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
     // gets decoded json product data
  private function getProducts()
    {
        $filePath = storage_path('app/products.json');

        if (!file_exists($filePath)) {
            return [];
        }

        return json_decode(file_get_contents($filePath), true) ?? [];
    }

    // save products in json file
      private function saveProducts($products)
    {
        $filePath = storage_path('app/products.json');

        file_put_contents(
            $filePath,
            json_encode($products, JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }

     //saves the product
    public function store(Request $request) 
    {
         // Validate form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $products = $this->getProducts();

         $newProduct = [
            'id' => uniqid(),
            'name' => $validated['name'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'created_at' => now()->toDateTimeString(),
        ];

        $products[] = $newProduct;

        $this->saveProducts($products);

       return response()->json([
        'message' => 'Product saved successfully!',
        'product' => $newProduct,
    ], 201);
    }

    // retrieves and displayes product
    public function index()
    {
        $products = $this->getProducts();

        // Sort by date, newest first
        usort($products, function ($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Calculate grand total
        $grandTotal = 0;

        foreach ($products as $product) {
            $grandTotal += $product['quantity'] * $product['price'];
        }

        return view('index', compact('products', 'grandTotal'));
    }

}
