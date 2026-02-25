<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($search = $request->input('buscar')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->filled('categoria')) {
            $query->where('category', $request->input('categoria'));
        }

        $productos  = $query->latest()->paginate(12)->withQueryString();
        $categorias = Product::whereNotNull('category')->distinct()->pluck('category')->sort()->values();

        return view('productos.index', compact('productos', 'categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'category'    => ['nullable', 'string', 'max:100'],
            'suggested'   => ['nullable', 'string'],
            'video'       => ['nullable', 'string', 'max:500'],
            'video_file'  => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/x-msvideo,video/quicktime,video/x-matroska,video/ogg', 'max:204800'],
            'image'       => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('productos', 'public');
        }

        if ($request->hasFile('video_file')) {
            $data['video'] = $request->file('video_file')->store('productos/videos', 'public');
        }

        unset($data['video_file']);
        $data['available'] = $request->boolean('available', true);

        Product::create($data);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'category'    => ['nullable', 'string', 'max:100'],
            'suggested'   => ['nullable', 'string'],
            'video'       => ['nullable', 'string', 'max:500'],
            'video_file'  => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/x-msvideo,video/quicktime,video/x-matroska,video/ogg', 'max:204800'],
            'image'       => ['nullable', 'image', 'max:5120'],
        ]);

        // Imagen
        if ($request->hasFile('image')) {
            if ($product->image) Storage::disk('public')->delete($product->image);
            $data['image'] = $request->file('image')->store('productos', 'public');
        } else {
            unset($data['image']);
        }

        // Video archivo (tiene prioridad sobre URL)
        if ($request->hasFile('video_file')) {
            if ($product->video_es_archivo) Storage::disk('public')->delete($product->video);
            $data['video'] = $request->file('video_file')->store('productos/videos', 'public');
        } elseif (empty($data['video'])) {
            // Campo URL vacío → mantener el valor actual
            unset($data['video']);
        }

        unset($data['video_file']);
        $data['available'] = $request->boolean('available');

        $product->update($data);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        if ($product->video_es_archivo) Storage::disk('public')->delete($product->video);

        $product->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
