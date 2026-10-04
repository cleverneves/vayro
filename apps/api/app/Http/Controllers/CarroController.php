<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarroRequest;
use App\Http\Requests\UpdateCarroRequest;
use App\Http\Resources\CarroResource;
use App\Models\Carro;
use Illuminate\Http\Request;

class CarroController extends Controller
{
    public function index(Request $request)
    {
        $carros = Carro::with('modelo')
            ->when($request->filled('disponivel'), fn ($query) => $query->where('disponivel', $request->boolean('disponivel')))
            ->paginate(15);

        return CarroResource::collection($carros);
    }

    public function store(StoreCarroRequest $request)
    {
        $carro = Carro::create($request->validated());

        return (new CarroResource($carro))->response()->setStatusCode(201);
    }

    public function show(Carro $carro)
    {
        return new CarroResource($carro->load('modelo'));
    }

    public function update(UpdateCarroRequest $request, Carro $carro)
    {
        $carro->update($request->validated());

        return new CarroResource($carro);
    }

    public function destroy(Carro $carro)
    {
        if ($carro->locacoes()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir um carro que possui locações cadastradas.',
            ], 409);
        }

        $carro->delete();

        return response()->noContent();
    }
}
