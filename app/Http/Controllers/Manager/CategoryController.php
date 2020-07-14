<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Helpers\FileUploader;
use Illuminate\Http\Request;
use App\Helpers\Doctrine;
use Doctrine_Manager;
use Categoria;

class CategoryController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $data['categorias'] = Doctrine::getTable('Categoria')->findAll();
        $data['title'] = 'Categorias';
        $data['content'] = view('manager.category.index', $data);

        return view('layouts.manager.app', $data);
    }

    /**
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id = null)
    {
        if ($id) {
            $categoria = Doctrine::getTable('Categoria')->find($id);
        } else {
            $categoria = new Categoria();
        }

        $data['categoria'] = $categoria;
        $data['title'] = $categoria->id ? 'Editar' : 'Crear';
        $data['content'] = view('manager.category.edit', $data);

        return view('layouts.manager.app', $data);
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Doctrine_Manager_Exception
     * @throws \Doctrine_Transaction_Exception
     * @throws \Doctrine_Validator_Exception
     * @throws \Exception
     */
    public function edit_form(Request $request, $id = null)
    {

        Doctrine_Manager::connection()->beginTransaction();

        try {
            if ($id)
                $categoria = Doctrine::getTable('Categoria')->find($id);
            else
                $categoria = new Categoria();

            $request->validate([
                'nombre' => 'required',
                'descripcion' => 'required',
                'logo' => 'required',
            ]);

            $respuesta = new \stdClass();

            // Cuenta
            $categoria->nombre = $request->input('nombre');
            $categoria->descripcion = $request->input('descripcion');
            //Si no 'nologo.png' es que cargo el logo por defecto, por lo tanto el campo
            //debe ser null.
            if ($request->input('logo') != "nologo.png") {
                $categoria->icon_ref = $request->input('logo');
            }

            $categoria->save();

            $id = (int)$categoria->id;

            if ($id > 0) {
                Doctrine_Manager::connection()->commit();

                $request->session()->flash('success', 'Categoria guardada con éxito.');
                $respuesta->validacion = true;
                $respuesta->redirect = url('manager/categorias');

            } else {
                $respuesta->validacion = false;
                $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>Ocurrió un error al guardar los datos.</div>';
                Doctrine_Manager::connection()->rollback();
            }

        } catch (Exception $ex) {
            $respuesta->validacion = false;
            $respuesta->errores = '<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a>' . $ex->getMessage() . '</div>';
            Doctrine_Manager::connection()->rollback();
        }

        return response()->json($respuesta);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Request $request, $id)
    {
        $categoria = Doctrine::getTable('Categoria')->find($id);
        $categoria->delete();

        $request->session()->flash('success', 'Categoria eliminada con éxito.');
        return redirect('manager/categorias');
    }


    /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadLogo(Request $request)
    {
        $allowedExtensions = ['jpg', 'png'];
        $pathLogos = public_path('logos/');
        $response = (new FileUploader($allowedExtensions))->handleUpload($pathLogos);

        return $response;
    }
    
        /**
     * @param Request $request
     * @return array
     */
    public function mySiteUploadLogof(Request $request)
    {
        $allowedExtensions = ['jpg', 'png'];
        $pathLogos = public_path('logos/');
        $response = (new FileUploader($allowedExtensions))->handleUpload($pathLogos);

        return $response;
    }

    /**
     * @param $title
     * @return mixed|string
     */
    public static function sanitize_folder_title($title)
    {
        $folder_title = trim($title);
        $folder_title = str_replace(' ', '-', $folder_title);
        $folder_title = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $folder_title);
        $folder_title = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $folder_title);
        $folder_title = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $folder_title);
        $folder_title = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $folder_title);
        $folder_title = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $folder_title);
        $folder_title = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $folder_title);
        $folder_title = str_replace(array("\\", "¨", "º", "~", "#", "@", "|", "!", "\"", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "`", "]", "+", "}", "{", "´", ">", "< ", ";", ",", ":"), '', $folder_title);
        $folder_title = substr($folder_title, 0, 45);

        return $folder_title;
    }

}