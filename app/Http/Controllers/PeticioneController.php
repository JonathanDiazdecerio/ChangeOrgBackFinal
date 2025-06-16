<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\File;
use App\Models\Peticione;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Validator;

class PeticioneController extends Controller
{
   public function index()
    {
        try{
            $peticiones = Peticione::with('file','user','categoria')->get();
            return $peticiones;
        }catch (\Exception $exception){
           return response()->json(['error'=>$exception->getMessage()]);
        }

    }
 public function listmine(){

        try{
            $user = Auth::user();
            //$id=1;
            $peticiones = Peticione::with('file', 'user', 'categoria')->where('user_id', $user->id)->get(); 
            return $peticiones;
        }catch (\Exception $exception){
            return response()->json(['error'=>$exception->getMessage()]);
        }

    }
public function listarfirmadas()
    {
        try {
            $user = Auth::user();
            $peticionesFirmadas = $user->firmas()->with('file', 'user', 'categoria')->get();
            return response()->json($peticionesFirmadas);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function show(Request $request, $id)
{
    try {
        $peticion = Peticione::with('file', 'user', 'categoria')->findOrFail($id);
        return response()->json(['message' => 'Petición encontrada', 'data' => $peticion], 200);
    } catch (Exception $e) {
        return response()->json(['error' => 'Petición no encontrada', 'debug' => $e->getMessage()], 404);
    }
}


    public function update(Request $request, $id)
{
    try {
        $peticion = Peticione::findOrFail($id);

        $this->authorize('update', $peticion);

        $peticion->update($request->all());
        return response()->json(['message' => 'Petición actualizada', 'data' => $peticion], 200);
    } catch (Exception $e) {
        return response()->json(['error' => 'No se pudo actualizar la petición', 'debug' => $e->getMessage()], 400);
    }
}


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|max:255',
            'descripcion' => 'required',
            'destinatario' => 'required',
            'categoria_id' => 'required|exists:categorias,id',
            'file' => 'required|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validación fallida', 'messages' => $validator->errors()], 422);
        }

        try {
            $input = $request->all();
            $category = Categoria::findOrFail($input['categoria_id']);
            $user = auth()->user();

            $peticion = new Peticione($input);
            $peticion->categoria()->associate($category);
            $peticion->user()->associate($user);
            $peticion->firmantes = 0;
            $peticion->estado = 'pendiente';

            if ($peticion->save()) {
                $res_file = $this->fileUpload($request, $peticion->id);

                if ($res_file instanceof File) {
                    $peticion->file = $res_file;
                } else {
                    return response()->json(['error' => 'Error al subir archivo', 'details' => $res_file], 500);
                }

                return response()->json([
                    'message' => 'Petición creada correctamente',
                    'data' => $peticion,
                    'file' => $peticion->file,
                ], 201);
            }

            return response()->json(['error' => 'No se pudo guardar la petición'], 400);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error al crear la petición',
                'debug' => $e->getMessage(),
            ], 500);
        }
    }
public function firmar(Request $request, $id)
{
    try {
        $peticion = Peticione::findOrFail($id);

        if ($request->user()->cannot('firmar', $peticion)) {
            return response()->json(['message' => 'Ya has firmado esta petición'], 403);
        }

        $user_id = auth()->id();
        $peticion->firmas()->attach($user_id);
        $peticion->firmantes += 1;
        $peticion->save();

        return response()->json(['message' => 'Petición firmada correctamente', 'data' => $peticion], 200);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Error al firmar la petición',
            'debug' => $e->getMessage(),
        ], 500);
    }
}


    public function cambiarEstado(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->estado = 'aceptada';
            $peticion->save();

            return response()->json(['message' => 'Estado actualizado', 'data' => $peticion], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo cambiar el estado de la petición',
                'debug' => $e->getMessage()
            ], 400);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $peticion = Peticione::findOrFail($id);
            $peticion->delete();

            return response()->json(['message' => 'Petición eliminada correctamente'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar la petición',
                'debug' => $e->getMessage()
            ], 400);
        }
    }

    public function fileUpload(Request $req, $peticione_id = null)
    {
        if (!$req->hasFile('file')) {
            return ['error' => 'No se recibió ningún archivo'];
        }

        $file = $req->file('file');

        if (!$file->isValid()) {
            return ['error' => 'Archivo no válido'];
        }

        $fileModel = new File;
        $fileModel->peticione_id = $peticione_id;

        try {
            $filename = time() . '_' . $file->getClientOriginalName();

            $file->storeAs('public/files', $filename);

            $fileModel->name = $filename;
            $fileModel->file_path = 'files/' . $filename;

            if (!$fileModel->save()) {
                return ['error' => 'No se pudo guardar la información del archivo en la base de datos'];
            }

            return $fileModel;

        } catch (Exception $e) {
            return ['error' => 'Error al subir archivo: ' . $e->getMessage()];
        }
    }
}
