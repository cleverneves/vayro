<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocacaoRequest;
use App\Http\Requests\UpdateLocacaoRequest;
use App\Http\Resources\LocacaoResource;
use App\Models\Locacao;
use Illuminate\Http\Request;

class LocacaoController extends Controller
{
    public function index(Request $request)
    {
        $locacoes = Locacao::with(['cliente', 'carro'])
            ->when($request->filled('cliente_id'), fn ($query) => $query->where('cliente_id', $request->cliente_id))
            ->when($request->filled('carro_id'), fn ($query) => $query->where('carro_id', $request->carro_id))
            ->paginate(15);

        return LocacaoResource::collection($locacoes);
    }

    public function store(StoreLocacaoRequest $request)
    {
        $locacao = Locacao::create($request->validated());

        return (new LocacaoResource($locacao))->response()->setStatusCode(201);
    }

    public function show(Locacao $locacao)
    {
        return new LocacaoResource($locacao->load(['cliente', 'carro']));
    }

    public function update(UpdateLocacaoRequest $request, Locacao $locacao)
    {
        $locacao->update($request->validated());

        return new LocacaoResource($locacao);
    }

    public function destroy(Locacao $locacao)
    {
        $locacao->delete();

        return response()->noContent();
    }
}
