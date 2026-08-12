<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarcaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Http\Resources\MarcaResource;
use App\Models\Marca;
use Illuminate\Support\Facades\Storage;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::with('modelos')->paginate(15);

        return MarcaResource::collection($marcas);
    }

    public function store(StoreMarcaRequest $request)
    {
        $dados = $request->validated();
        $dados['imagem'] = $request->file('imagem')->store('imagens', 'public');

        $marca = Marca::create($dados);

        return (new MarcaResource($marca))->response()->setStatusCode(201);
    }

    public function show(Marca $marca)
    {
        return new MarcaResource($marca->load('modelos'));
    }

    public function update(UpdateMarcaRequest $request, Marca $marca)
    {
        $dados = $request->validated();

        if ($request->hasFile('imagem')) {
            Storage::disk('public')->delete($marca->imagem);
            $dados['imagem'] = $request->file('imagem')->store('imagens', 'public');
        }

        $marca->update($dados);

        return new MarcaResource($marca);
    }

    public function destroy(Marca $marca)
    {
        if ($marca->modelos()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir uma marca que possui modelos cadastrados.',
            ], 409);
        }

        Storage::disk('public')->delete($marca->imagem);
        $marca->delete();

        return response()->noContent();
    }
}
