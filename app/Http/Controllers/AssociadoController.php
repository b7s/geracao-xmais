<?php

namespace App\Http\Controllers;

use App\Models\Associado;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AssociadoController extends Controller
{
    /**
     * Verifica se já existe um associado com o mesmo celular ou email
     */
    public function verificarUnicidade(Request $request): JsonResponse
    {
        // Limpa os dados de entrada
        $celular = $request->celular ? preg_replace('/[^0-9]/', '', $request->celular) : null;
        $email = $request->email ? trim($request->email) : null;
        
        $validator = Validator::make([
            'celular' => $celular,
            'email' => $email,
            'id' => $request->id
        ], [
            'celular' => 'nullable|string',
            'email' => 'nullable|email',
            'id' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Pelo menos um dos campos deve ser fornecido
        if (empty($celular) && empty($email)) {
            return response()->json(['errors' => ['general' => 'Celular ou email devem ser fornecidos.']], 422);
        }

        $existeCelular = false;
        $existeEmail = false;
        $mensagem = null;

        // Verifica celular se fornecido
        if (!empty($celular)) {
            $queryCelular = Associado::where('celular', $celular);
            
            if ($request->has('id') && $request->id) {
                $queryCelular->where('id', '!=', $request->id);
            }
            
            $existeCelular = $queryCelular->exists();
            
            if ($existeCelular) {
                $mensagem = 'Já existe um associado com este número de celular.';
            }
        }

        // Verifica email se fornecido
        if (!empty($email)) {
            $queryEmail = Associado::where('email', $email);
            
            if ($request->has('id') && $request->id) {
                $queryEmail->where('id', '!=', $request->id);
            }
            
            $existeEmail = $queryEmail->exists();
            
            if ($existeEmail && !$existeCelular) {
                $mensagem = 'Já existe um associado com este endereço de email.';
            } else if ($existeEmail && $existeCelular) {
                $mensagem = 'Já existe um associado com este celular e email.';
            }
        }

        return response()->json([
            'existe' => $existeCelular || $existeEmail,
            'mensagem' => $mensagem
        ]);
    }
} 