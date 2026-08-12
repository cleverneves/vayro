<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeloRequest;
use App\Http\Requests\UpdateModeloRequest;
use App\Http\Resources\ModeloResource;
use App\Models\Modelo;
use Illuminate\Support\Facades\Storage;

class ModeloController extends Controller
{
    public function index()
    {
        $modelos = Modelo::with('marca')->paginate(15);

        return ModeloResource::collection($modelos);
    }

    public function store(StoreModeloRequest $request)
    {
        $dados = $request->validated();
        $dados['imagem'] = $request->file('imagem')->store('imagens/modelos', 'public');

        $modelo = Modelo::create($dados);

        return (new ModeloResource($modelo))->response()->setStatusCode(201);
    }

    public function show(Modelo $modelo)
    {
        return new ModeloResource($modelo->load('marca'));
    }

    public function update(UpdateModeloRequest $request, Modelo $modelo)
    {
        $dados = $request->validated();

        if ($request->hasFile('imagem')) {
            Storage::disk('public')->delete($modelo->imagem);
            $dados['imagem'] = $request->file('imagem')->store('imagens/modelos', 'public');
        }

        $modelo->update($dados);

        return new ModeloResource($modelo);
    }

    public function destroy(Modelo $modelo)
    {
        if ($modelo->carros()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir um modelo que possui carros cadastrados.',
            ], 409);
        }

        Storage::disk('public')->delete($modelo->imagem);
        $modelo->delete();

        return response()->noContent();
    }
}
