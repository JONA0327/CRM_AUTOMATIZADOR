<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($search = $request->input('buscar')) {
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('observation', 'like', "%{$search}%");
        }

        if ($request->filled('estado')) {
            $query->where('status', $request->input('estado'));
        }

        $clientes = $query->latest()->paginate(15)->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'date'        => ['nullable', 'date'],
            'status'      => ['nullable', 'string', 'max:50'],
            'observation' => ['nullable', 'string', 'max:1000'],
        ]);

        Client::create($data);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function update(Request $request, Client $cliente)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'date'        => ['nullable', 'date'],
            'status'      => ['nullable', 'string', 'max:50'],
            'observation' => ['nullable', 'string', 'max:1000'],
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
}
