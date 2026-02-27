<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientObservation;
use App\Models\Product;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount('observations');

        if ($search = $request->input('buscar')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('folio', 'like', "%{$search}%");
        }

        if ($request->filled('estado')) {
            $query->where('status', $request->input('estado'));
        }

        $clientes = $query->latest()->paginate(15)->withQueryString();

        $productos = Product::orderBy('name')->get()->map(fn ($p) => [
            'id'        => $p->id,
            'name'      => $p->name,
            'image_url' => $p->image_url,
        ]);

        return view('clientes.index', compact('clientes', 'productos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'date'   => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $client = Client::create($data);
        $client->update(['folio' => 'CLI-' . str_pad($client->id, 4, '0', STR_PAD_LEFT)]);

        return redirect()->route('clientes.index')
            ->with('success', "Cliente creado con folio {$client->folio}.");
    }

    public function update(Request $request, Client $cliente)
    {
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'date'   => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $cliente->update($data);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }

    public function consulta(Request $request)
    {
        $cliente     = null;
        $productosMap = collect();
        $notFound    = false;

        if ($buscar = trim($request->input('buscar', ''))) {
            $cliente = Client::with(['observations' => fn ($q) => $q->latest()])
                ->where('folio', $buscar)
                ->orWhere('phone', 'like', "%{$buscar}%")
                ->first();

            if ($cliente) {
                $productosMap = Product::orderBy('name')->get()->keyBy('name')->map(fn ($p) => [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'description' => $p->description,
                    'price'       => $p->price,
                    'category'    => $p->category,
                    'available'   => $p->available,
                    'image_url'   => $p->image_url,
                    'video_url'   => $p->video_url,
                ]);
            } else {
                $notFound = true;
            }
        }

        return view('consulta.index', compact('cliente', 'productosMap', 'notFound', 'buscar'));
    }

    public function show(Client $cliente)
    {
        $cliente->load(['observations' => fn ($q) => $q->latest()]);

        // Mapa de productos indexado por nombre para búsqueda rápida en JS
        $productosMap = Product::orderBy('name')->get()->keyBy('name')->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description,
            'price'       => $p->price,
            'category'    => $p->category,
            'available'   => $p->available,
            'image_url'   => $p->image_url,
            'video_url'   => $p->video_url,
        ]);

        return view('clientes.show', compact('cliente', 'productosMap'));
    }

    // ── Observaciones (JSON) ──────────────────────────────────────────────────

    public function getObservaciones(Client $cliente)
    {
        return response()->json(
            $cliente->observations()->latest()->get()
        );
    }

    public function storeObservacion(Request $request, Client $cliente)
    {
        $data = $request->validate([
            'weight'             => ['nullable', 'numeric', 'min:0', 'max:999'],
            'age'                => ['nullable', 'integer', 'min:0', 'max:120'],
            'observation'        => ['nullable', 'string', 'max:3000'],
            'suggested_products' => ['nullable', 'string', 'max:1000'],
        ]);

        $obs = $cliente->observations()->create($data);

        return response()->json($obs, 201);
    }

    public function updateObservacion(Request $request, Client $cliente, ClientObservation $observacion)
    {
        $data = $request->validate([
            'weight'             => ['nullable', 'numeric', 'min:0', 'max:999'],
            'age'                => ['nullable', 'integer', 'min:0', 'max:120'],
            'observation'        => ['nullable', 'string', 'max:3000'],
            'suggested_products' => ['nullable', 'string', 'max:1000'],
        ]);

        $observacion->update($data);

        return response()->json($observacion);
    }

    public function destroyObservacion(Client $cliente, ClientObservation $observacion)
    {
        $observacion->delete();

        return response()->json(['ok' => true]);
    }
}
