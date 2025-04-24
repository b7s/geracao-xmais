<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AssociadoController extends Controller
{
    /**
     * Verifica se já existe um associado com o mesmo celular e data de nascimento
     */
    public function verificarUnicidade(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'celular' => 'required',
            'data_nascimento' => 'required|date',
            'id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Associado::where('celular', $request->celular)
            ->where('data_nascimento', $request->data_nascimento);
        
        if ($request->has('id') && $request->id) {
            $query->where('id', '!=', $request->id);
        }

        $existe = $query->exists();

        return response()->json([
            'existe' => $existe,
            'mensagem' => $existe ? 'Já existe um associado com este celular e data de nascimento.' : null
        ]);
    }
} 