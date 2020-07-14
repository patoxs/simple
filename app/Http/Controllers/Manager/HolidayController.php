<?php

namespace App\Http\Controllers\Manager;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Httpful\Request as RequestHttp;

class HolidayController extends Controller
{
    private $base_services = '';
    private $context = '';

    public function __construct()
    {
        $this->base_services = env('BASE_SERVICE');
        $this->context = env('CONTEXT_SERVICE');
        $agendaTemplate = RequestHttp::init()
            ->expectsJson()
            ->addHeaders(array(
                'appkey' => env('AGENDA_APP_KEY')
            ));
        RequestHttp::ini($agendaTemplate);
    }

    public function index()
    {
        $data['cuentas'] = '';
        $data['title'] = 'Días Feriados';
        $data['content'] = view('manager.holiday.index', $data);
        return view('layouts.manager.app', $data);
    }

    public function EmptyCalendar()
    {
        $var = '{"success": 1,"result": []}';
        echo $var;
    }

    public function diasFeriados()
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        //$year=(isset($_GET['year']) && is_numeric($_GET['year']) && $_GET['year']>0 )?$_GET['year']:date('Y');
        try {
            $uri = $this->base_services . '' . $this->context . 'daysOff';
            Log::debug('diasFeriados URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('diasFeriados Response ' . $response);
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                $code = $response->code;
                $code = $response->body[0]->response->code;
                $mensaje = $response->body[0]->response->message;
                foreach ($response->body[1]->daysoff as $item) {
                    $tmp = date('d-m-Y', strtotime($item->date_dayoff));
                    $data[] = array('date_dayoff' => $tmp, 'name' => $item->name, 'id' => $item->id);
                }
            }
        } catch (Exception $err) {
            Log::error('diasFeriados ' . $err);
            $mensaje = $err->getMessage();
        }

        $array = array('code' => $code, 'message' => $mensaje, 'daysoff' => $data);
        return response()->json($array);
    }

    public function ajax_dia_conf_global($fecha)
    {
        $data['fecha'] = $fecha;
        $this->load->view('manager/diaferiado/ajax_dia_calendario', $data);
    }

    public function ajax_agregar_dia_feriado()
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        $fecha = (isset($_GET['fecha']) && !empty($_GET['fecha'])) ? $_GET['fecha'] : '';
        $name = (isset($_GET['name'])) ? $_GET['name'] : '';
        if (!empty($fecha)) {
            $json = '{
                "date_dayoff": "' . $fecha . '",
                "name": "' . $name . '"
                }';
            try {
                $uri = $this->base_services . '' . $this->context . 'daysOff';
                Log::debug('ajax_agregar_dia_feriado URI ' . $uri);
                $response = RequestHttp::post($uri)->body($json)->sendIt();
                Log::debug('ajax_agregar_dia_feriado Response ' . $response);
                $code = $response->code;
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    $code = $response->body[0]->response->code;
                    $mensaje = $response->body[0]->response->message;
                } else {
                    if (isset($response->body->response->code)) {
                        $code = $response->body->response->code;
                        switch ($code) {
                            case '1080':
                                $mensaje = 'No se puede agregar este día festivo porque ya existen citas para este día';
                                break;
                            default:
                                $mensaje = $response->body->response->message;
                                break;
                        }
                    }
                }
            } catch (Exception $err) {
                Log::error('ajax_agregar_dia_feriado ' . $err);
                $mensaje = $err->getMessage();
            }
        } else {
            $mensaje = 'No se pudo ingresar el día el parametro de fecha a ingresar es incorrecto';
        }
        $array = array('code' => $code, 'mensaje' => $mensaje, 'daysoff' => $data);
        echo json_encode($array);
    }

    public function ajax_confirmar_eliminar_dia()
    {
        $data['selecciono'] = (isset($_GET['select']) && isset($_GET['fecha']) && !empty($_GET['fecha'])) ? $_GET['select'] : 0;
        $data['fecha'] = (isset($_GET['fecha'])) ? $_GET['fecha'] : '';
        $data['id'] = (isset($_GET['id'])) ? $_GET['id'] : '';
        $this->load->view('manager/diaferiado/ajax_confirmar_eliminar_dia', $data);
    }

    public function ajax_eliminar_dia_feriado()
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        $id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : 0;
        if ($id > 0) {
            try {
                $uri = $this->base_services . '' . $this->context . 'daysOff/' . $id;
                Log::debug('ajax_eliminar_dia_feriado URI ' . $uri);
                $response = RequestHttp::delete($uri)->sendIt();
                Log::debug('ajax_eliminar_dia_feriado Response ' . $response);
                $code = $response->code;
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    $code = $response->body[0]->response->code;
                    $mensaje = $response->body[0]->response->message;
                } else {
                    if (isset($response->body->response->code)) {
                        $code = $response->body->response->code;
                        $mensaje = $response->body->response->message;
                    }
                }
            } catch (Exception $err) {
                Log::error('ajax_eliminar_dia_feriado ' . $err);
                $mensaje = $err->getMessage();
            }
        } else {
            $mensaje = 'No se pudo eliminar el día, el parametro de la fecha a eliminar es incorrecto';
        }
        $array = array('code' => $code, 'mensaje' => $mensaje, 'daysoff' => $data);
        echo json_encode($array);
    }
}
