<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::active()->get();
        $productsByCategory = $products->groupBy('category');
        $catalog = Product::getProductCatalog();
        
        return view('products.index', compact('productsByCategory', 'catalog'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'catalog' => Product::getProductCatalog(),
            'countries' => Product::getAmericanCountries()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'name' => 'required|string|max:255',
            'key_points' => 'array',
            'key_points.*' => 'string',
            'information' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:153600', // 150MB en KB
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:153600', // 150MB en KB
            'disease' => 'nullable|string',
            'country' => 'required|string',
            'dosage_preventivo' => 'nullable|string',
            'dosage_correctivo' => 'nullable|string',
            'dosage_cronico' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['category', 'name', 'information', 'disease', 'country']);
        
        // Procesar puntos clave
        $data['key_points'] = $request->key_points ?? [];
        
        // Procesar dosis
        $data['dosage'] = [
            'preventivo' => $request->dosage_preventivo,
            'correctivo' => $request->dosage_correctivo,
            'cronico' => $request->dosage_cronico
        ];

        // Procesar imagen
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $data['image'] = $imageData;
            $data['image_name'] = $image->getClientOriginalName();
        }

        // Procesar video
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $videoData = base64_encode(file_get_contents($video->getRealPath()));
            $data['video'] = $videoData;
            $data['video_name'] = $video->getClientOriginalName();
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'product' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return response()->json([
            'success' => true,
            'product' => $product,
            'catalog' => Product::getProductCatalog(),
            'countries' => Product::getAmericanCountries()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'name' => 'required|string|max:255',
            'key_points' => 'array',
            'key_points.*' => 'string',
            'information' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:153600',
            'video' => 'nullable|file|mimes:mp4,avi,mov,wmv|max:153600',
            'disease' => 'nullable|string',
            'country' => 'required|string',
            'dosage_preventivo' => 'nullable|string',
            'dosage_correctivo' => 'nullable|string',
            'dosage_cronico' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['category', 'name', 'information', 'disease', 'country']);
        
        // Procesar puntos clave
        $data['key_points'] = $request->key_points ?? [];
        
        // Procesar dosis
        $data['dosage'] = [
            'preventivo' => $request->dosage_preventivo,
            'correctivo' => $request->dosage_correctivo,
            'cronico' => $request->dosage_cronico
        ];

        // Procesar imagen si se actualiza
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $data['image'] = $imageData;
            $data['image_name'] = $image->getClientOriginalName();
        }

        // Procesar video si se actualiza
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $videoData = base64_encode(file_get_contents($video->getRealPath()));
            $data['video'] = $videoData;
            $data['video_name'] = $video->getClientOriginalName();
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente',
            'product' => $product->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(Request $request)
    {
        $category = $request->input('category');
        $catalog = Product::getProductCatalog();
        
        return response()->json([
            'products' => $catalog[$category] ?? []
        ]);
    }
}
