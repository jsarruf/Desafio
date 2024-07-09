<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'sale_value' => 'required|numeric',
            'stock' => 'required|integer',
            'images' => 'array',
            'images.*' => 'file|image|max:2048'
        ]);

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
        }

        $product = Product::create([
            'description' => $data['description'],
            'sale_value' => $data['sale_value'],
            'stock' => $data['stock'],
            'images' => json_encode($images)
        ]);

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        return $product;
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'sale_value' => 'sometimes|required|numeric',
            'stock' => 'sometimes|required|integer',
            'images' => 'array',
            'images.*' => 'file|image|max:2048'
        ]);

        if ($request->hasFile('images')) {
            // Deletar as imagens antigas
            foreach (json_decode($product->images) as $image) {
                Storage::disk('public')->delete($image);
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $images[] = $path;
            }
            $product->images = json_encode($images);
        }

        $product->update($data);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        // Deletar as imagens do produto
        foreach (json_decode($product->images) as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
