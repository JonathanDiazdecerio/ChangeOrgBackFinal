<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Categoria;
use Exception;

class CategoriaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|unique:categorias',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'Message' => 'Error de validación',
                    'Errors' => $validator->errors()
                ], 422);
            }

            $categoria = Categoria::create($request->all());

            return response()->json([
                'Message' => 'La categoría se ha creado correctamente',
                'Data' => $categoria
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al crear la categoría',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }
public function index()
{
    return Categoria::all();
}

    public function show($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return response()->json([
                'Message' => 'Categoría encontrada con éxito',
                'Data' => $categoria
            ]);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'No se pudo encontrar la categoría solicitada',
                'Debug' => $e->getMessage()
            ], 404);
        }
    }

    public function list()
    {
        try {
            $categorias = Categoria::all();
            return response()->json([
                'Message' => 'Listado de categorías',
                'Categorias' => $categorias
            ]);
        } catch (Exception $e) {
            return response()->json([
                'Message' => 'Error al obtener las categorías',
                'Debug' => $e->getMessage()
            ], 500);
        }
    }
}
