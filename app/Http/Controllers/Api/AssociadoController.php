<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Associado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssociadoController extends Controller
{
    /**
     * Verifica se já existe um associado com o mesmo celular e data de nascimento
     */
    public function verificarUnicidade(Request $request): JsonResponse
    {
        $request->validate([
            'celular' => 'required|string',
            'data_nascimento' => 'required|date',
            'id' => 'nullable|integer'
        ]);

        $celular = $request->input('celular');
        $dataNascimento = $request->input('data_nascimento');
        $id = $request->input('id');

        $query = Associado::query()
            ->where('celular', $celular)
            ->where('data_nascimento', $dataNascimento);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        $existe = $query->exists();

        return response()->json([
            'existe' => $existe,
            'mensagem' => $existe ? 'Já existe um associado com este celular e data de nascimento.' : null,
        ]);
    }
} 