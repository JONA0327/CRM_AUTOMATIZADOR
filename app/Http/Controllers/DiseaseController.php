<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiseaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Disease::query();

        if ($search = $request->input('buscar')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%");
        }

        if ($request->filled('categoria')) {
            $query->where('category', $request->input('categoria'));
        }

        $enfermedades = $query->latest()->paginate(12)->withQueryString();
        $categorias   = Disease::whereNotNull('category')->distinct()->pluck('category')->sort()->values();
        $productos    = Product::orderBy('name')->pluck('name');

        return view('enfermedades.index', compact('enfermedades', 'categorias', 'productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'description'=> ['nullable', 'string'],
            'category'   => ['nullable', 'string', 'max:100'],
            'symptoms'   => ['nullable', 'string'],
            'treatment'  => ['nullable', 'string'],
            'prevention' => ['nullable', 'string'],
            'suggested'  => ['nullable', 'string'],
            'image'      => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('enfermedades', 'public');
        }

        Disease::create($data);

        return redirect()->route('enfermedades.index')
            ->with('success', 'Enfermedad creada correctamente.');
    }

    public function update(Request $request, Disease $enfermedad)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'description'=> ['nullable', 'string'],
            'category'   => ['nullable', 'string', 'max:100'],
            'symptoms'   => ['nullable', 'string'],
            'treatment'  => ['nullable', 'string'],
            'prevention' => ['nullable', 'string'],
            'suggested'  => ['nullable', 'string'],
            'image'      => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($enfermedad->image) Storage::disk('public')->delete($enfermedad->image);
            $data['image'] = $request->file('image')->store('enfermedades', 'public');
        } else {
            unset($data['image']);
        }

        $enfermedad->update($data);

        return redirect()->route('enfermedades.index')
            ->with('success', 'Enfermedad actualizada correctamente.');
    }

    public function destroy(Disease $enfermedad)
    {
        if ($enfermedad->image) Storage::disk('public')->delete($enfermedad->image);

        $enfermedad->delete();

        return redirect()->route('enfermedades.index')
            ->with('success', 'Enfermedad eliminada correctamente.');
    }
}
