<?php

namespace App\Http\Controllers;

use App\Rules\CheckDocument;
use Illuminate\Http\Request;
use App\Models\File;


class ValidatorController extends Controller
{
    public function index()
    {
        return view('validator.document');
    }

    public function documento(Request $request)
    {
        $request->validate([
            'id' => ['required', new CheckDocument($request)],
            'key' => 'required'
        ], [
            'id.required' => 'El campo Folio es obligatorio.',
            'key.required' => 'El campo Código de verificación es obligatorio.'
        ]);


        $idFile = str_replace(' ', '', $request->input('id'));
        $llavecopiaFile = str_replace(' ', '',  $request->input('key'));

        $file = File::where('id', $idFile)
            ->where('llave_copia', $llavecopiaFile)
            ->orderBy('id', 'desc')
            ->first();

        $path = 'uploads/documentos/' . $file->filename;

        if (!file_exists(public_path($path))) {
            return view('validator.document');
        }

        return response()->file($path);
    }

    public function documentoQR(Request $request)
    {
        $id = $request->input('id', null);
        $key = $request->input('key', null);

        try {
            $file = File::where('id', $id)
                ->where('llave_copia', $key)
                ->orderBy('id', 'desc')->first();
        } catch (Exception $e) {
            Log::info('==> Error al intentar validar con CODIGO y KEY para file: '.$id);
            return abort(500);
        } catch (\ErrorException $e) {
            Log::info('==> Error al intentar validar con CODIGO y KEY para file: '.$id);
            return abort(500);
        }

        if ($file) {
            $path = 'uploads/documentos/' . $file->filename;
            if (!file_exists(public_path($path))) {
                return view('validator.document');
            }

            return response()->file($path);
        }

        return abort(404);
    }

}