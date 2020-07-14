<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\Doctrine;
use Doctrine_Query;
use Httpful\Request as RequestHttp;

class AppointmentController extends Controller
{
    private $base_services = '';
    private $context = '';
    private $records = 10;

    public function __construct()
    {
        $this->base_services = env('BASE_SERVICE');
        $this->context = env('CONTEXT_SERVICE');
        $recordsc = env('RECORDS');
        $this->records = empty($recordsc) ? 10 : $recordsc;
        $cuenta = \Cuenta::cuentaSegunDominio()->id;
        try {
            $service = new \Connect_services();
            $service->setCuenta($cuenta);
            $service->load_data();

            $agendaTemplate = RequestHttp::init()
                //->expectsJson()
                ->addHeaders(array(
                    'appkey' => $service->getAppkey(),
                    'domain' => $service->getDomain()
                ));
            RequestHttp::ini($agendaTemplate);

        } catch (Exception $err) {
            Log::error('Constructor' . $err);
            //echo 'Error: '.$err->getMessage();
        }
    }


    private function confirmar_cita($id)
    {
        try {
            $uri = $this->base_services . '' . $this->context . 'appointments/confirm/' . $id;
            Log::debug('confirmar_cita URI ' . $uri);
            $response = RequestHttp::put($uri)->sendIt();
            $code = $response->code;
            if ($code == 200) {
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    $code = $response->body[0]->response->code;
                    $appointment = $response->body[1]->id;
                }
            } else {
                throw new Exception('La cita reservada ya no esta disponible, reserve una nueva hora.');
            }
        } catch (Exception $err) {
            Log::error('confirmar_cita' . $err);
            throw new Exception($err->getMessage());
        }
    }

    private function obtenerTiempoCita($idagenda)
    {
        $valor = 0;
        try {
            $uri = $this->base_services . '' . $this->context . 'calendars/' . $idagenda; //url del servicio con los parametros
            Log::debug('obtenerTiempoCita URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                $valor = $response->body[1]->calendars[0]->time_attention;
            }
        } catch (Exception $err) {
            Log::error('obtenerTiempoCita ' . $err);
        }
        return $valor;
    }

    private function validateDate($date, $format = 'Y-m-d')
    {
        try {
            $val = explode("-", $date);
            if (isset($val[0]) && isset($val[1]) && isset($val[2])) {
                if (checkdate($val[1], $val[2], $val[0])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $err) {
            Log::error('validateDate ' . $err);
            return false;
        }
    }

    private function validarTipoUsuario()
    {
        try {
            $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
            $uri = $this->base_services . '' . $this->context . 'calendars/listByOwner/' . $id;
            Log::debug('validarTipoUsuario URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('validarTipoUsuario Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code == 200) {
                if (count($response->body[1]->calendars) > 0) {
                    return true;
                } else {
                    $sw = $this->validar_agenda_grupos($id);
                    return $sw;
                }
            } else {
                $sw = $this->validar_agenda_grupos($id);
                return $sw;
            }
        } catch (Exception $err) {
            Log::error('validarTipoUsuario ' . $err);
            return false;
        }
    }

    private function validar_agenda_grupos($id)
    {
        $usuario = Doctrine::getTable('Usuario')->findByid($id);
        $sw = false;
        foreach ($usuario[0]->GruposUsuarios as $g) {
            try {
                $uri = $this->base_services . '' . $this->context . 'calendars/listByOwner/' . $g->id;
                Log::debug('validar_agenda_grupos URI ' . $uri);
                $response = RequestHttp::get($uri)->sendIt();
                Log::debug('validar_agenda_grupos Response ' . $response);
                $code = $response->code;
                if (isset($response->code) && $response->code == 200 && isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    if (count($response->body[1]->calendars) > 0) {
                        $sw = true;
                    } else {
                        $sw = false;
                    }
                } else {
                    $sw = false;
                }
            } catch (Exception $err) {
                Log::error('validar_agenda_grupos ' . $err);
                throw new Exception($err->getMessage());
            }
        }
        return $sw;
    }

    private function obtenerAgendas($owner)
    {
        $result = array();
        try {
            $uri = $this->base_services . '' . $this->context . 'calendars/listByOwner/' . $owner;
            Log::debug('obtenerAgendas URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('obtenerAgendas response ' . $response);
            $code = $response->code;
            if (isset($response->code) && $response->code == 200 && isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                foreach ($response->body[1]->calendars as $item) {
                    $tmp = new \stdClass();
                    $tmp->id = $item->id;
                    $tmp->name = $item->name;
                    $tmp->owner_id = $item->owner_id;
                    $tmp->owner_name = $item->owner_name;
                    $tmp->owner_email = $item->owner_email;
                    $tmp->is_group = $item->is_group;
                    $tmp->schedule = $item->schedule;
                    $tmp->time_attention = $item->time_attention;
                    $tmp->concurrency = $item->concurrency;
                    $tmp->ignore_non_working_days = $item->ignore_non_working_days;
                    $tmp->time_cancel_appointment = $item->time_cancel_appointment;
                    $tmp->time_confirm_appointment = $item->time_confirm_appointment;
                    $result[] = $tmp;
                }
            }
        } catch (Exception $err) {
            Log::error('obtenerAgendas ' . $err);
            throw new Exception($err->getMessage());
        }
        $usuario = Doctrine::getTable('Usuario')->findByid($owner);
        foreach ($usuario[0]->GruposUsuarios as $g) {
            try {
                $uri = $this->base_services . '' . $this->context . 'calendars/listByOwner/' . $g->id;
                $response = RequestHttp::get($uri)->sendIt();
                $code = $response->code;
                if (isset($response->code) && $response->code == 200 && isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    foreach ($response->body[1]->calendars as $item) {
                        $tmp = new \stdClass();
                        $tmp->id = $item->id;
                        $tmp->name = $item->name;
                        $tmp->owner_id = $item->owner_id;
                        $tmp->owner_name = $item->owner_name;
                        $tmp->owner_email = $item->owner_email;
                        $tmp->is_group = $item->is_group;
                        $tmp->schedule = $item->schedule;
                        $tmp->time_attention = $item->time_attention;
                        $tmp->concurrency = $item->concurrency;
                        $tmp->ignore_non_working_days = $item->ignore_non_working_days;
                        $tmp->time_cancel_appointment = $item->time_cancel_appointment;
                        $tmp->time_confirm_appointment = $item->time_confirm_appointment;
                        $result[] = $tmp;
                    }
                }
            } catch (Exception $err) {
                Log::error('obtenerAgendas ' . $err);
                throw new Exception($err->getMessage());
            }
        }
        return $result;
    }

    public function confirmar_citas_grupo($ids)
    {
        try {
            Log::debug('confirmar_citas_grupo Input ' . $ids);
            $uri = $this->base_services . '' . $this->context . 'appointments/bulkConfirm';
            Log::debug('confirmar_citas_grupo URI ' . $uri);
            $response = RequestHttp::post($uri)->body($ids)->sendIt();
            Log::debug('confirmar_citas_grupo Response ' . $response);
            $code = $response->code;
            if ($code == 200) {
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    $code = $response->body[0]->response->code;
                    $appointment = $response->body[1]->id;
                }
            } else {
                throw new Exception('La cita reservada ya no esta disponible. Por favor, reserve una nueva hora.');
            }
        } catch (Exception $err) {
            Log::error('confirmar_citas_grupo' . $err);
            throw new Exception($err->getMessage());
        }
    }

    public function disponibilidad($idagenda = 0)
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        $tramite = '';
        try {
            $tiempofin = $this->obtenerTiempoCita($idagenda);
            $date = (isset($_GET['date'])) ? $_GET['date'] : '';
            if (!empty($date)) {
                $uri = $this->base_services . '' . $this->context . 'appointments/availability/' . $idagenda . '?date=' . $date;//url del servicio con los parametros
            } else {
                $uri = $this->base_services . '' . $this->context . 'appointments/availability/' . $idagenda;//url del servicio con los parametros
            }
            Log::debug('disponibilidad URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('disponibilidad response ' . $uri);
            $code = $response->code;

            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                $code = $response->body[0]->response->code;
                $mensaje = $response->body[0]->response->message;
                $concurrency = $response->body[1]->concurrency;
                $usuario = Doctrine_Query::create()
                    ->from("Campo")
                    ->where('agenda_campo=' . $idagenda)
                    ->execute();
                foreach ($usuario as $ob) {
                    $tramite = $ob->etiqueta;
                }
                foreach ($response->body[1]->appointmentsavailable as $keyd => $item) {
                    $object = get_object_vars($item);
                    if (is_array($object) && count($object) > 0) {
                        $fecha = $keyd;
                        foreach ($object as $keyhora => $dispo) {
                            $index = 1;
                            foreach ($dispo as $appontment) {
                                $id = $appontment->applier_name;
                                $tmp = strtotime($fecha . ' ' . $keyhora . ':00', time());
                                $end = strtotime('+' . $tiempofin . ' minute', strtotime($fecha . ' ' . $keyhora . ':00')) * 1000;
                                $start = $tmp * 1000;
                                $title = $appontment->applier_name;
                                $available = $appontment->available;
                                $correo = $appontment->applier_email;
                                $clsevent = '';
                                switch ($available) {
                                    case 'D':
                                        $clsevent = 'event-warning';
                                        $title = 'Disponible';
                                        break;
                                    case 'R':
                                        $clsevent = 'event-info';
                                        $title = 'Reservado';
                                        break;
                                    case 'B':
                                        $clsevent = 'event-success';
                                        $title = 'Bloqueado';
                                        break;
                                }
                                $title = $title . ' ' . $fecha . ' ' . $keyhora . ':00 ' . $id . ' ' . $correo;
                                $fechahora = date('d/m/Y', strtotime($fecha)) . ' ' . $keyhora . ':00 ';
                                $fechap = date('d/m/Y', strtotime($fecha));
                                $hora = $keyhora . ':00 ';
                                $cita = $appontment->appointment_id;
                                //$cita=0;
                                //$data[]=array('id'=>$id,'title'=>$title,'url'=>'#','class'=>$clsevent,'start'=>$start,'end'=>$end,'estado'=>$available,'concurrencia'=>$concurrency,'cuenta'=>$index,'correo'=>$correo,'block_id'=>$appontment->block_id,'tramite'=>$tramite,'fechahora'=>$fechahora);
                                $data[] = array('id' => $id, 'title' => $title, 'url' => '#', 'class' => $clsevent, 'start' => $start, 'end' => $end, 'estado' => $available, 'concurrencia' => $concurrency, 'cuenta' => $index, 'correo' => $correo, 'block_id' => $appontment->block_id, 'tramite' => $tramite, 'fecha' => $fechap, 'hora' => $hora, 'cita' => $cita);
                                $index++;
                            }
                        }
                    }
                }
            }
        } catch (Exception $err) {
            Log::error('disponibilidad ' . $err);
            $mensaje = $err->getMessage();
            print $mensaje;
        }
        $result = array('success' => 1, 'result' => $data);
        echo json_encode($result);
    }

    public function disponibilidadCiudadano($idagenda)
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        try {
            $tiempofin = $this->obtenerTiempoCita($idagenda);
            $date = (isset($_GET['date'])) ? $_GET['date'] : '';
            if (!empty($date)) {
                $uri = $this->base_services . '' . $this->context . 'appointments/availability/' . $idagenda . '?date=' . $date;//url del servicio con los parametros
            } else {
                $uri = $this->base_services . '' . $this->context . 'appointments/availability/' . $idagenda;//url del servicio con los parametros
            }
            Log::debug('disponibilidadCiudadano URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('disponibilidadCiudadano response ' . $uri);
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                $code = $response->body[0]->response->code;
                $mensaje = $response->body[0]->response->message;
                $concurrency = $response->body[1]->concurrency;
                $fechasagregadas = array();
                foreach ($response->body[1]->appointmentsavailable as $keyd => $item) {
                    $object = get_object_vars($item);
                    if (is_array($object) && count($object) > 0) {
                        $fecha = $keyd;
                        foreach ($object as $keyhora => $dispo) {
                            $index = 1;
                            if ($concurrency > 1) {// se verifica si la concurrencia es mayor a 1
                                foreach ($dispo as $appontment) {
                                    //$id=$appontment->applier_name;
                                    $tmp = strtotime($fecha . ' ' . $keyhora . ':00', time());
                                    $end = strtotime('+' . $tiempofin . ' minute', strtotime($fecha . ' ' . $keyhora . ':00')) * 1000;
                                    $start = $tmp * 1000;
                                    $id = $start;
                                    $title = $appontment->applier_name;
                                    $available = $appontment->available;
                                    $clsevent = '';
                                    switch ($available) {
                                        case 'D':
                                            $clsevent = 'event-warning';
                                            $title = 'Disponible';
                                            break;
                                        case 'R':
                                            //$clsevent='event-info';
                                            $clsevent = 'event-success';
                                            $title = 'Reservado';
                                            break;
                                        case 'B':
                                            $clsevent = 'event-success';
                                            $title = 'Bloqueado';
                                            break;
                                    }
                                    $title = $title . ' ' . $fecha . ' ' . $keyhora . ':00';
                                    if (!in_array($start, $fechasagregadas)) {
                                        $fechasagregadas[] = $start;
                                        $data[] = array('id' => $id, 'title' => $title, 'url' => '#', 'class' => $clsevent, 'start' => $start, 'end' => $end, 'estado' => $available, 'concurrencia' => $concurrency, 'cuenta' => $index);
                                        $index++;
                                    } else {

                                        if ($available == 'D') {
                                            $clave = array_search($start, $fechasagregadas);
                                            $data[$clave] = array('id' => $id, 'title' => $title, 'url' => '#', 'class' => $clsevent, 'start' => $start, 'end' => $end, 'estado' => $available, 'concurrencia' => $concurrency, 'cuenta' => $index);
                                        }
                                    }
                                }
                            } else {
                                foreach ($dispo as $appontment) {
                                    $id = $appontment->applier_name;
                                    $tmp = strtotime($fecha . ' ' . $keyhora . ':00', time());
                                    $end = strtotime('+' . $tiempofin . ' minute', strtotime($fecha . ' ' . $keyhora . ':00')) * 1000;
                                    $start = $tmp * 1000;
                                    $title = $appontment->applier_name;
                                    $available = $appontment->available;
                                    $clsevent = '';
                                    switch ($available) {
                                        case 'D':
                                            $clsevent = 'event-warning';
                                            $title = 'Disponible';
                                            break;
                                        case 'R':
                                            //$clsevent='event-info';
                                            $clsevent = 'event-success';
                                            $title = 'Reservado';
                                            break;
                                        case 'B':
                                            $clsevent = 'event-success';
                                            $title = 'Bloqueado';
                                            break;
                                    }
                                    $title = $title . ' ' . $fecha . ' ' . $keyhora . ':00';
                                    $data[] = array('id' => $id, 'title' => $title, 'url' => '#', 'class' => $clsevent, 'start' => $start, 'end' => $end, 'estado' => $available, 'concurrencia' => $concurrency, 'cuenta' => $index);
                                    $index++;
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $err) {
            Log::error('disponibilidadCiudadano ' . $err);
            $mensaje = $err->getMessage();
            print $mensaje;
        }
        $result = array('success' => 1, 'result' => $data);
        echo json_encode($result);
    }

    public function obtener_citas_de_tramite($etapa)
    {
        $result = array();
        try {
            $rstramite = Doctrine_Query::create()
                ->select('tramite_id')
                ->from('Etapa')
                ->where("id=?", $etapa)
                ->execute();
            $idtramite = 0;

            foreach ($rstramite as $obj) {
                $idtramite = $obj->tramite_id;
            }

            $rsvalores = Doctrine_Query::create()
                ->select('ds.valor')
                ->from('DatoSeguimiento ds,Etapa e')
                ->where("ds.etapa_id=e.id AND e.tramite_id=?", $idtramite)
                ->execute();

            foreach ($rsvalores as $obj2) {
                if (isset($obj2->valor) && is_string($obj2->valor)) {
                    //dd($obj2->valor);
                    $val = str_replace('"', '', $obj2->valor);
                    $val = trim($val);
                    if (is_string($val) && !empty($val)) {
                        $arrval = explode('_', $val);
                        if (isset($arrval[1])) {
                            if ($this->validateDate($arrval[1])) {
                                $result[] = $arrval[0];
                            }
                        }
                    }
                }
            }
            return $result;
        } catch (Exception $err) {
            Log::debug('obtener_citas_de_tramite ' . $err);
            throw new Exception('No se pudo confirmar si en su proceso existen citas, vuelva a intentarlo');
        }
    }

    public function miagenda($pagina = 1)
    {

        Log::debug('miagenda');

        if (!Auth::user()->registrado) {
            $this->session->set_flashdata('redirect', current_url());
            redirect('autenticacion/login');
        }

        $tipousuario = $this->validarTipoUsuario();
        $agendas = array();
        $agenda = (isset($_POST['cmbagenda']) && is_numeric($_POST['cmbagenda']) && $_POST['cmbagenda'] > 0) ? $_POST['cmbagenda'] : 0;
        if ($tipousuario) {
            try {
                $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
                $agendas = $this->obtenerAgendas($id);//
                $data['agendas'] = $agendas;
                if ($agenda == 0) {
                    $agenda = $agendas[0]->id;
                }
            } catch (Exception $err) {
                Log::error('miagenda ' . $err);
                // echo $err->getMessage();
            }
        }
        $data['pagina'] = $pagina;
        $total_registros = 0;
        $registros = $this->records; // numero de registro a mostrar por pagina
        $num_paginas = 5; // numeros maximo de paginas a mostrar en la lista del paginador
        $inicio = ($pagina - 1) * $registros; // se calcula desde que registro se empieza a mostrar
        $finreg = $inicio + $registros; // se calcula hasta que registro se muestra
        $pagina_intervalo = ceil($num_paginas / 2) - 1;
        $pagina_desde = $pagina - $pagina_intervalo;
        $pagina_hasta = $pagina + $pagina_intervalo;
        $data['pagina_intervalo'] = $pagina_intervalo;
        $data['pagina_desde'] = $pagina_desde;
        $data['pagina_hasta'] = $pagina_hasta;
        if ($agenda > 0) {
            $arraydata = $this->ajax_listar_citas_agenda($pagina, $agenda);
        } else {
            $arraydata = $this->ajax_listar_citas($pagina, $tipousuario);
        }
        $datos = array();
        if ($arraydata['code'] == 200) {
            $datos = $arraydata['data'];
            $total_registros = empty($arraydata['count']) ? 0 : $arraydata['count'];
        }
        $data['agenda'] = $agenda;
        $total_paginas = ceil($total_registros / $registros); // calculo de total de paginas.
        $total_paginas = ($total_paginas != 0) ? $total_paginas : 1;
        $data['data'] = $datos;
        $data['total_paginas'] = $total_paginas;

        // true si es funcionario o false si es un ciudadano
        if ($tipousuario) {
            // $idagenda = (isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
            $data['idagenda'] = $agenda;
            $data['title'] = 'Mi Agenda';
            $data['sidebar'] = 'miagenda';
            $data['content'] = 'agenda/miagenda_funcionario';
            $this->load->view('template_newhome', $data);

        } else {
            $data['title'] = 'Mi Agenda';
            $data['sidebar'] = 'miagenda';
            $data['content'] = 'agenda/miagenda';
            $this->load->view('template_newhome', $data);
        }
    }

    public function miagenda2($pagina = 1)
    {

        Log::debug('miagenda');

        if (!Auth::user()->registrado) {
            $this->session->set_flashdata('redirect', current_url());
            redirect('autenticacion/login');
        }

        $tipousuario = $this->validarTipoUsuario();
        $agendas = array();
        $agenda = (isset($_POST['cmbagenda']) && is_numeric($_POST['cmbagenda']) && $_POST['cmbagenda'] > 0) ? $_POST['cmbagenda'] : 0;
        if ($tipousuario) {
            try {
                $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
                $agendas = $this->obtenerAgendas($id);//
                $data['agendas'] = $agendas;
                if ($agenda == 0) {
                    $agenda = $agendas[0]->id;
                }
            } catch (Exception $err) {
                Log::error('miagenda ' . $err);
                // echo $err->getMessage();
            }
        }
        $data['pagina'] = $pagina;
        $total_registros = 0;
        $registros = $this->records; // numero de registro a mostrar por pagina
        $num_paginas = 5; // numeros maximo de paginas a mostrar en la lista del paginador
        $inicio = ($pagina - 1) * $registros; // se calcula desde que registro se empieza a mostrar
        $finreg = $inicio + $registros; // se calcula hasta que registro se muestra
        $pagina_intervalo = ceil($num_paginas / 2) - 1;
        $pagina_desde = $pagina - $pagina_intervalo;
        $pagina_hasta = $pagina + $pagina_intervalo;
        $data['pagina_intervalo'] = $pagina_intervalo;
        $data['pagina_desde'] = $pagina_desde;
        $data['pagina_hasta'] = $pagina_hasta;
        if ($agenda > 0) {
            $arraydata = $this->ajax_listar_citas_agenda($pagina, $agenda);
        } else {
            $arraydata = $this->ajax_listar_citas($pagina, $tipousuario);
        }
        $datos = array();
        if ($arraydata['code'] == 200) {
            $datos = $arraydata['data'];
            $total_registros = empty($arraydata['count']) ? 0 : $arraydata['count'];
        }
        $data['agenda'] = $agenda;
        $total_paginas = ceil($total_registros / $registros); // calculo de total de paginas.
        $total_paginas = ($total_paginas != 0) ? $total_paginas : 1;
        $data['data'] = $datos;
        $data['total_paginas'] = $total_paginas;

        // true si es funcionario o false si es un ciudadano
        if ($tipousuario) {
            // $idagenda = (isset($_GET['idagenda']) && is_numeric($_GET['idagenda']))?$_GET['idagenda']:0;
            $data['idagenda'] = $agenda;
            $data['title'] = 'Mi Agenda';
            $data['sidebar'] = 'miagenda';
            $data['content'] = 'agenda/miagenda_funcionario';
            $this->load->view('template', $data);

        } else {
            $data['title'] = 'Mi Agenda';
            $data['sidebar'] = 'miagenda';
            $data['content'] = 'agenda/miagenda';
            $this->load->view('template', $data);
        }
    }

    public function diasFeriados()
    {
        Log::info('[INI][diasFeriados]');
        $code = 0;
        $msg = '';
        $data_tmp = array();
        $year_service = date('Y');
        Log::debug('$year_service [' . $year_service . ']');
        for ($i = 0; $i < 2; $i++) {
            try {
                Log::debug('============ [INI] Consulta Servicio daysOff Año ' . $year_service . ' ===');
                // Url del servicio con los parametros
                $uri = $this->base_services . '' . $this->context . 'daysOff?year=' . $year_service;
                Log::debug('diasFeriados URI ' . $uri);
                Log::debug('diasFeriados $data_tmp ' . json_encode($data_tmp));

                $response = RequestHttp::get($uri)->sendIt();
                Log::debug('diasFeriados Response ' . $response);

                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    Log::debug('$response->body[0]->response->code [' . $response->body[0]->response->code . ']');
                    $msg = $response->body[0]->response->message;
                    Log::debug('$response->body[0]->response->message [' . $response->body[0]->response->message . ']');

                    foreach ($response->body[1]->daysoff as $item) {
                        $tmp = date('d-m-Y', strtotime($item->date_dayoff));
                        $data_tmp[] = array('date_dayoff' => $tmp, 'name' => $item->name);
                    }
                }
                Log::debug('=== [END] Consulta Servicio daysOff Año ' . $year_service . ' ===');
            } catch (Exception $err) {
                $msg = $err->getMessage();
                Log::error('diasFeriados ' . $err);
            }
            $year_service++;
        }

        if (isset($data_tmp) && sizeof($data_tmp) > 0) {
            $code = '200';
        } else {
            $code = '404';
            $msg = 'No hay días feriados';
        }

        $array = array('code' => $code, 'message' => $msg, 'daysoff' => $data_tmp);
        Log::info('[END][diasFeriados] result : ' . json_encode($array));

        echo json_encode($array);
    }

    public function cargarCitasCalendar()
    {
        $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
        $data = array();
        try {
            $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
            $uri = $this->base_services . '' . $this->context . 'appointments/listByOwner/' . $id;//url del servicio con los parametros
            Log::debug('cargarCitasCalendar URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('cargarCitasCalendar Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code == 200) {
                foreach ($response->body[1]->appointments as $item) {
                    $timetmp = strtotime($item->appointment_time);
                    $start = $timetmp * 1000;
                    $end = strtotime('+12 minute', $timetmp) * 1000;
                    $title = $item->applier_name;
                    $data[] = array('id' => $item->appointment_id, 'title' => $title, 'url' => '#', 'class' => 'event-info', 'start' => $start, 'end' => $end);
                }
            } else {
                $mensaje = 'No se pudo cancelar la cita intentelo mas tarde.';

            }
        } catch (Exception $err) {
            Log::error('cargarCitasCalendar ' . $response);
            $mensaje = $err->getMessage();
        }
        $result = array('success' => 1, 'result' => $data);
        echo json_encode($result);
    }

    public function bloqueo()
    {
        $start = (isset($_GET['start'])) ? $_GET['start'] : 0;
        $end = (isset($_GET['end'])) ? $_GET['end'] : 0;
        $id = (isset($_GET['id'])) ? $_GET['id'] : 0;
        $data['start'] = $start;
        $data['end'] = $end;
        $data['id'] = $id;
        $this->load->view('agenda/ajax_confirmar_agregar_bloqueo', $data);
    }

    public function desbloqueo()
    {
        $data['id'] = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : 0;
        $this->load->view('agenda/ajax_confirmar_eliminar_bloqueo', $data);
    }

    public function ajax_eliminar_bloqueo()
    {
        $code = 0;
        $mensaje = 0;
        $idbloqueo = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : 0;
        try {
            $uri = $this->base_services . '' . $this->context . 'blockSchedules/' . $idbloqueo;
            Log::debug('ajax_eliminar_bloqueo URI ' . $uri);
            $response = RequestHttp::delete($uri)->sendIt();
            Log::debug('ajax_eliminar_bloqueo Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && isset($response->body->response->code) && $response->body->response->code == 200) {
                $code = $response->body->response->code;
                $mensaje = $response->body->response->message;
            } else {
                $code = 0;
                $mensaje = 'No se pudo eliminar el bloqueo por favor vuelva a intentarlo, si el problema persiste informe al administrador.';
            }
        } catch (Exception $err) {
            Log::error('ajax_eliminar_bloqueo ' . $response);
            $mensaje = $err->getMessage();
        }
        echo json_encode(array('code' => $code, 'mensaje' => $mensaje));
    }

    public function ajax_modal_calendar()
    {
        $idagenda = (isset($_GET['idagenda']) && is_numeric($_GET['idagenda'])) ? $_GET['idagenda'] : 0;
        $idobject = (isset($_GET['object']) && is_numeric($_GET['object'])) ? $_GET['object'] : 0;
        $idcita = (isset($_GET['idcita']) && is_numeric($_GET['idcita'])) ? $_GET['idcita'] : 0;
        $idtramite = (isset($_GET['idtramite']) && is_numeric($_GET['idtramite'])) ? $_GET['idtramite'] : 0;
        $etapa = (isset($_GET['etapa']) && is_numeric($_GET['etapa'])) ? $_GET['etapa'] : 0;
        $data['idagenda'] = $idagenda;
        $data['idobject'] = $idobject;
        $data['idcita'] = $idcita;
        $data['idtramite'] = $idtramite;
        $data['etapa'] = $etapa;

        return view('agenda.calendario_ciudadano', $data);
    }

    public function ajax_confirmar_agregar_dia()
    {
        $idagenda = (isset($_GET['idagenda'])) ? $_GET['idagenda'] : 0;
        $idtramite = (isset($_GET['idtramite'])) ? $_GET['idtramite'] : 0;
        $etapa = (isset($_GET['etapa'])) ? $_GET['etapa'] : 0;
        $fecha = (isset($_GET['fecha'])) ? $_GET['fecha'] : '';
        $hora = (isset($_GET['hora'])) ? $_GET['hora'] : '';
        $idcita = (isset($_GET['idcita'])) ? $_GET['idcita'] : '';
        $fechaf = (isset($_GET['fechaf'])) ? $_GET['fechaf'] : '';
        $horaf = (isset($_GET['horaf'])) ? $_GET['horaf'] : '';
        $object = (isset($_GET['obj'])) ? $_GET['obj'] : 0;
        $tmp = explode('-', $fecha);
        $data['dia'] = $tmp[2];
        $data['mes'] = $tmp[1];
        $data['ano'] = $tmp[0];
        $data['hora'] = $hora;
        $data['idagenda'] = $idagenda;
        $data['idcita'] = $idcita;
        $data['object'] = $object;
        $data['idtramite'] = $idtramite;
        $data['etapa'] = $etapa;
        $data['fechafinal'] = $fechaf . ' ' . $horaf;
        $this->load->view('agenda/ajax_confirmar_agregar_dia', $data);
    }

    public function ajax_agregar_cita()
    {
        $id = Auth::user()->id;
        $nombre = Auth::user()->nombres . ' ' . Auth::user()->apellido_paterno . ' ' . Auth::user()->apellido_materno;
        $trimNombre = trim($nombre);
        $nombre = (!empty($trimNombre)) ? $nombre : 'Cliente';
        $idagenda = (isset($_GET['idagenda'])) ? $_GET['idagenda'] : '';
        $tzz = (isset($_GET['tzz'])) ? $_GET['tzz'] : '';
        $fecha = (isset($_GET['fecha'])) ? $_GET['fecha'] : '';
        $fechafinal = (isset($_GET['fechafinal'])) ? $_GET['fechafinal'] : '';
        $desc = (isset($_GET['desc'])) ? $_GET['desc'] : '';
        $email = (isset($_GET['email']) && !empty($_GET['email'])) ? $_GET['email'] : Auth::user()->email;
        $appointment = (isset($_GET['idcita'])) ? $_GET['idcita'] : 0;
        $idetapa = (isset($_GET['idtramite'])) ? $_GET['idtramite'] : 0;
        $obj = (isset($_GET['obj'])) ? $_GET['obj'] : 0;
        $code = 0;
        $defaultTimeZone = new DateTimeZone(date_default_timezone_get());//'America/Santiago'
        $browserTimeZone = empty($tzz) ? $defaultTimeZone : new DateTimeZone($tzz);
        $fechaCal = new DateTime($fecha, $browserTimeZone);
        $fechaCal->setTimezone($defaultTimeZone);
        $fechaformat = $fechaCal->format('Y-m-d H:i:sP');

        $mensaje = '';
        $nomproceso = '';
        $et = Doctrine_Query::create()
            ->from("Etapa")
            ->where('id=' . $idetapa)
            ->execute();
        foreach ($et as $ob) {
            $idtramite = $ob->tramite_id;
        }
        $ttram = Doctrine::getTable('Tramite')->findByid($idtramite);
        $nomproceso = $ttram[0]->Proceso->nombre;
        $metavalue = '{"tramite":"' . $idtramite . '","etapa":"' . $idetapa . '","nombre_tramite":"' . $nomproceso . '","calendario_id":"' . $idagenda . '","idcampo":"' . $obj . '" }';
        $json = '{
                "applier_email": "' . $email . '",
                "applier_id": "' . $id . '",
                "applier_name": "' . $nombre . '",
                "appointment_start_time": "' . $fechaformat . '",
                "calendar_id": "' . $idagenda . '",
                "subject": "' . $desc . '"
                }';
        if ($appointment > 0) {//0 reserva una cita y 1 edita una cita
            try {
                $uri = $this->base_services . '' . $this->context . 'appointments/' . $appointment;
                Log::debug('ajax_agregar_cita URI ' . $uri);
                $responsever = RequestHttp::get($uri)->sendIt();
                $code = $responsever->code;
                if (isset($responsever->body) && is_array($responsever->body) && isset($responsever->body[0]->response->code) && ($responsever->body[0]->response->code == 200)) {//Se verifica si existe la cita.
                    //Si la cita existe se procede a consumir el servicio de actualizar.
                    try {
                        $uri = $this->base_services . '' . $this->context . 'appointments/' . $appointment;
                        $responsever = RequestHttp::put($uri)->sendIt();
                        $response = RequestHttp::put($uri)->body($json)->sendIt();
                        $code = $response->code;
                        if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                            $code = $response->body[0]->response->code;
                            $mensaje = $response->body[0]->response->message;
                            $appointment = $response->body[1]->id;
                        } else {
                            $code = $response->body->response->code;
                            switch ($code) {
                                case "2040":
                                    $mensaje = 'El tiempo seleccionado no se encuentra disponible en el calendario.';
                                    break;
                                case "1020":
                                    $mensaje = 'El email de la persona es requerido.';
                                    break;
                            }
                        }
                    } catch (Exception $err) {
                        Log::error('ajax_agregar_cita ' . $err);
                        $mensaje = $response->body[0]->response->message;
                    }
                } else {
                    //echo 'no existe';
                    //Si la cita no existe se procede a consumir el servicio de reservar
                    $json = '{
                        "applier_email": "' . $email . '",
                        "applier_id": "' . $id . '",
                        "applier_name": "' . $nombre . '",
                        "appointment_start_time": "' . $fechaformat . '",
                        "calendar_id": "' . $idagenda . '",
                        "subject": "' . $desc . '",
                        "metadata":' . $metavalue . '
                        }';
                    try {
                        $uri = $this->base_services . '' . $this->context . 'appointments/reserve';//url del servicio con los parametros
                        Log::debug('ajax_agregar_cita URI ' . $uri);
                        $response = RequestHttp::post($uri)->body($json)->sendIt();
                        $code = $response->code;
                        if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                            $code = $response->body[0]->response->code;
                            $mensaje = $response->body[0]->response->message;
                            $appointment = $response->body[1]->id;
                        } else {
                            $code = (isset($response->body->response->code)) ? $response->body->response->code : 0;
                            $mensaje = (isset($response->body->response->message)) ? $response->body->response->message : 'Error General';
                        }
                    } catch (Exception $err) {
                        Log::error('ajax_agregar_cita ' . $err);
                        $mensaje = 'No se pudo reservar la cita, volverlo a intentar.';
                        //$mensaje=$response->body[0]->response->message;
                    }
                }
            } catch (Exception $err) {
                Log::error('ajax_agregar_cita ' . $err);
            }
        } else {
            $json = '{
                "applier_email": "' . $email . '",
                "applier_id": "' . $id . '",
                "applier_name": "' . $nombre . '",
                "appointment_start_time": "' . $fechaformat . '",
                "calendar_id": "' . $idagenda . '",
                "subject": "' . $desc . '",
                "metadata":' . $metavalue . '
                }';
            try {
                $uri = $this->base_services . '' . $this->context . 'appointments/reserve';//url del servicio con los parametros
                Log::debug('ajax_agregar_cita URI ' . $uri);
                $response = RequestHttp::post($uri)->body($json)->sendIt();
                $code = $response->code;
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    $code = $response->body[0]->response->code;
                    $mensaje = $response->body[0]->response->message;
                    $appointment = $response->body[1]->id;
                } else {
                    $code = (isset($response->body->response->code)) ? $response->body->response->code : 0;
                    $mensaje = (isset($response->body->response->message)) ? $response->body->response->message : 'Error General';
                }
            } catch (Exception $err) {
                Log::error('ajax_agregar_cita ' . $err);
                $mensaje = 'No se pudo reservar la cita. Por favor, inténtelo de nuevo.';
            }
        }
        $tmpfecha = explode(' ', $fecha);
        $tmp = explode('-', $tmpfecha[0]);
        $fres = $tmp[2] . '/' . $tmp[1] . '/' . $tmp[0] . ' ' . $tmpfecha[1];
        echo json_encode(array('code' => $code, 'mensaje' => $mensaje, 'appointment' => $appointment, 'fecha' => $fres));
    }

    public function ajax_listar_citas_agenda($pagina, $agenda)
    {
        $datos = array();
        $total_registros = 0;
        $code = 0;
        $mensaje = '';
        try {
            $uri = $this->base_services . '' . $this->context . 'appointments/listByCalendar/' . $agenda . '?page=' . $pagina;
            Log::debug('ajax_listar_citas_agenda URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('ajax_listar_citas_agenda Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code == 200) {
                //$datos=$response->body->appointments;
                foreach ($response->body[1]->appointments as $items) {
                    $class = $newobj = new \stdClass();
                    $class->appointment_id = $items->appointment_id;
                    $class->subject = $items->subject;
                    $class->owner_name = $items->owner_name;
                    $class->appointment_time = $items->appointment_time;
                    $class->applier_attended = $items->applier_attended;
                    $class->calendar_id = $items->calendar_id;
                    $class->applier_email = $items->applier_email;
                    $class->applier_name = trim($items->applier_name);
                    $metadata = json_decode($items->metadata);
                    $class->idtramite = (isset($metadata->tramite) && is_numeric($metadata->tramite)) ? $metadata->tramite : 0;
                    $class->etapa = (isset($metadata->etapa) && is_numeric($metadata->etapa)) ? $metadata->etapa : 0;
                    $class->idcampo = (isset($metadata->idcampo) && is_numeric($metadata->idcampo)) ? $metadata->idcampo : 0;
                    $proceso = Doctrine_Query::create()
                        ->select('p.nombre')
                        ->from("Proceso p,Tramite t")
                        ->where('p.id=t.proceso_id AND p.activo=1 AND t.id=?', $class->idtramite)
                        ->execute();
                    $nombre = '';
                    foreach ($proceso as $ob) {
                        $nombre = $ob->nombre;
                    }
                    $class->tramite = $nombre;


                    $datos[] = $class;
                }
            }
        } catch (Exception $err) {
            Log::error('ajax_listar_citas_agenda response ' . $err);
            $mensaje = $err->getMessage();
        }
        $array = array('code' => $code, 'message' => $mensaje, 'data' => $datos, 'count' => $total_registros);
        return $array;
    }

    public function ajax_listar_citas($pagina, $tipousuario)
    {
        $datos = array();
        $total_registros = 0;
        $code = 0;
        $mensaje = '';
        try {
            $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
            //$usuario= Doctrine::getTable('Usuario')->findByid($id);
            if ($tipousuario) {
                $uri = $this->base_services . '' . $this->context . 'appointments/listByOwner/' . $id . '?page=' . $pagina;
            } else {
                $uri = $this->base_services . '' . $this->context . 'appointments/listByApplier/' . $id . '?page=' . $pagina;
            }
            Log::debug('ajax_listar_citas URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('ajax_listar_citas Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code == 200) {
                $total_registros = $response->body[1]->count;
                foreach ($response->body[1]->appointments as $items) {
                    $class = $newobj = new \stdClass();
                    $class->appointment_id = $items->appointment_id;
                    $class->subject = $items->subject;
                    $class->owner_name = $items->owner_name;
                    $class->appointment_time = $items->appointment_time;
                    $class->applier_attended = $items->applier_attended;
                    $class->calendar_id = $items->calendar_id;
                    $class->applier_email = $items->applier_email;
                    $class->applier_name = trim($items->applier_name);
                    $metadata = json_decode($items->metadata);
                    $class->idtramite = (isset($metadata->tramite) && is_numeric($metadata->tramite)) ? $metadata->tramite : 0;
                    $class->etapa = (isset($metadata->etapa) && is_numeric($metadata->etapa)) ? $metadata->etapa : 0;
                    $class->idcampo = (isset($metadata->idcampo) && is_numeric($metadata->idcampo)) ? $metadata->idcampo : 0;
                    $proceso = Doctrine_Query::create()
                        ->select('p.nombre')
                        ->from("Proceso p,Tramite t")
                        ->where('p.id=t.proceso_id AND p.activo=1 AND t.id=?', $class->idtramite)
                        ->execute();
                    $nombre = '';
                    foreach ($proceso as $ob) {
                        $nombre = $ob->nombre;
                    }
                    $class->tramite = $nombre;

                    $datos[] = $class;
                }
            }
        } catch (Exception $err) {
            Log::error('ajax_listar_citas_agenda ' . $err);
            $mensaje = $err->getMessage();
        }
        $array = array('code' => $code, 'message' => $mensaje, 'data' => $datos, 'count' => $total_registros);
        return $array;
    }

    public function ajax_obtener_datos_agenda()
    {
        $code = 0;
        $mensaje = 0;
        $id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? $_GET['id'] : 0;
        $agenda = '';
        try {
            $uri = $this->base_services . '' . $this->context . 'calendars/' . $id;//url del servicio con los parametros
            Log::debug('ajax_obtener_datos_agenda URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('ajax_obtener_datos_agenda Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code == 200) {
                $agenda = $response->body[1]->calendars[0];
            } else {
                $code = 0;
                $mensaje = 'No se pudo obtener la validacion de ignorar feriados.';
                Log::debug('ajax_obtener_datos_agenda ' . $mensaje);
            }
        } catch (Exception $err) {
            Log::error('ajax_obtener_datos_agenda ' . $err);
            $mensaje = $err->getMessage();
        }
        echo json_encode(array('code' => $code, 'mensaje' => $mensaje, 'calendar' => $agenda));
    }

    public function ajax_cancelarCita($appoint_id)
    {
        $code = 0;
        $mensaje = '';
        try {
            $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
            $nombre = Auth::user()->nombres . ' ' . Auth::user()->apellido_paterno . ' ' . Auth::user()->apellido_materno;
            $motivo = (isset($_GET['motivo'])) ? $_GET['motivo'] : 'Cancelado por el ciudadano';
            $json = '{
                "cancelation_cause": "' . $motivo . '",
                "user_id_cancel": "' . $id . '",
                "user_name_cancel": "' . $nombre . '"
                }';
            $uri = $this->base_services . '' . $this->context . 'appointments/cancel/' . $appoint_id;//url del servicio con los parametros
            Log::debug('ajax_cancelarCita URI ' . $uri);
            $response = RequestHttp::put($uri)->body($json)->sendIt();
            Log::debug('ajax_cancelarCita Response ' . $response);
            $code = $response->code;
            if (isset($response->body->response->code) && $response->body->response->code == 200) {
                $code = $response->body->response->code;
                $mensaje = 'Ok';
            } else {
                $code = $response->body->response->code;
                $mensaje = $response->body->response->message;
                switch ($code) {
                    case "2060":
                        $mensaje = 'No se puede cancelar la cita porque ya excedio el tiempo minimo para cancelarla';
                        break;
                }
            }
        } catch (Exception $err) {
            Log::error('ajax_cancelarCita ' . $err);
            $mensaje = $err->getMessage();
        }
        $array = array('code' => $code, 'message' => $mensaje);
        echo json_encode($array);
    }

    public function ajax_cancelar_cita_funcionario($idcita)
    {
        $data['idcita'] = $idcita;
        $this->load->view('agenda/ajax_cancelar_cita_funcionario', $data);
    }

    public function ajax_agregar_bloqueo_dia_completo()
    {
        $code = 0;
        $mensaje = '';
        $idagenda = (isset($_GET['idagenda']) && is_numeric($_GET['idagenda'])) ? $_GET['idagenda'] : 0;
        $causa = (isset($_GET['razon'])) ? $_GET['razon'] : '';

        if (count($_GET['horainicio']) >= 1) {
            $rage = '[';
            for ($i = 0; $i < count($_GET['horainicio']); $i++) {
                $fechainicio = date(DATE_ATOM, $_GET['horainicio'][$i] / 1000);
                $fechafinal = date(DATE_ATOM, $_GET['horafinal'][$i] / 1000);
                if ($i == 0) {
                    $rage = $rage . '{
                        "start_date": "' . $fechainicio . '",
                        "end_date": "' . $fechafinal . '"
                    }';
                } else {
                    $rage = $rage . ',{
                        "start_date": "' . $fechainicio . '",
                        "end_date": "' . $fechafinal . '"
                    }';
                }
            }
            $rage = $rage . ']';
            $usuario = (isset(Auth::user()->usuario)) ? Auth::user()->usuario : '';
            $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
            $json = '{
                "calendar_id": "' . $idagenda . '",
                "user_id_block": "' . $id . '",
                "user_name_block":"' . $usuario . '",
                "range":' . $rage . ',
                "cause": "' . $causa . '"
            }';
            if ($idagenda > 0) {
                try {
                    $uri = $this->base_services . '' . $this->context . 'blockSchedules/bulkCreate';
                    Log::debug('ajax_agregar_bloqueo_dia_completo URI ' . $uri);
                    $response = RequestHttp::post($uri)->body($json)->sendIt();
                    Log::debug('ajax_agregar_bloqueo_dia_completo Response ' . $response);
                    $code = $response->code;
                    if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                        $code = $response->body[0]->response->code;
                        $mensaje = $response->body[0]->response->message;
                    } else {
                        if (isset($response->body->response->code) && is_numeric($response->body->response->code)) {
                            $code = $response->body->response->code;
                            switch ($response->body->response->code) {
                                case '2090':
                                    $mensaje = 'No puede bloquear una fecha/hora menor a la actual';
                                    $mensaje = $response->body->response->message;
                                    break;
                                default:
                                    $mensaje = (isset($response->body->response->message)) ? $response->body->response->message : 'No se pudo bloquear intentelo mas tarde.';
                                    break;
                            }
                        } else {
                            $mensaje = 'No se pudo bloquear vuelvalo a intentar, si el problema persiste contacte con el administrador';
                        }
                    }
                } catch (Exception $err) {
                    $mensaje = 'No se pudo bloquear vuelvalo a intentar, si el problema persiste contacte con el administrador';
                }
            }
        } else {
            $mensaje = 'No hay horas para bloquear';
            //echo 'no hay horas para bloquear';
        }
        echo json_encode(array('code' => $code, 'mensaje' => $mensaje));
    }

    public function ajax_agregar_bloqueo()
    {
        $code = 0;
        $mensaje = '';
        $idagenda = (isset($_GET['idagenda']) && is_numeric($_GET['idagenda'])) ? $_GET['idagenda'] : 0;
        $fechainicio = (isset($_GET['fechainicio'])) ? $_GET['fechainicio'] : '';
        $fechafinal = (isset($_GET['fechafinal'])) ? $_GET['fechafinal'] : '';
        $causa = (isset($_GET['razon'])) ? $_GET['razon'] : '';
        $tmpfecha = date('Y-m-d', strtotime($fechainicio));
        if ($tmpfecha == date('Y-m-d')) {
            $tfini = date('Y-m-d H:i');
            $fechainicio = date('Y-m-d H:i', strtotime('+5 minute', strtotime($tfini)));
        }
        $fechainicio = date(DATE_ATOM, strtotime($fechainicio));
        $fechafinal = date(DATE_ATOM, strtotime($fechafinal));
        $id = (isset(Auth::user()->id)) ? Auth::user()->id : 0;
        $usuario = (isset(Auth::user()->usuario)) ? Auth::user()->usuario : '';
        $json = '{
            "calendar_id":"' . $idagenda . '",
            "cause":"' . $causa . '",
            "end_date":"' . $fechafinal . '",
            "start_date":"' . $fechainicio . '",
            "user_id_block":"' . $id . '",
            "user_name_block":"' . $usuario . '"
        }';
        if ($idagenda > 0) {
            try {
                $uri = $this->base_services . '' . $this->context . 'blockSchedules';
                Log::debug('ajax_agregar_bloqueo URI ' . $uri);
                $response = RequestHttp::post($uri)->body($json)->sendIt();
                Log::debug('ajax_agregar_bloqueo Response ' . $response);
                $code = $response->code;
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                    $code = $response->body[0]->response->code;
                    $mensaje = $response->body[0]->response->message;
                } else {
                    if (isset($response->body->response->code) && is_numeric($response->body->response->code)) {
                        $code = $response->body->response->code;
                        switch ($response->body->response->code) {
                            case '2090':
                                $mensaje = 'No puede bloquear una fecha/hora menor a la actual';
                                $mensaje = $response->body->response->message;
                                break;
                            default:
                                $mensaje = (isset($response->body->response->message)) ? $response->body->response->message : 'No se pudo bloquear intentelo mas tarde.';
                                break;
                        }
                    } else {
                        $mensaje = 'No se pudo bloquear vuelvalo a intentar, si el problema persiste contacte con el administrador';
                    }
                }
            } catch (Exception $err) {
                Log::debug('ajax_agregar_bloqueo ' . $err);
                $mensaje = 'No se pudo bloquear vuelvalo a intentar, si el problema persiste contacte con el administrador';
            }
        }
        echo json_encode(array('code' => $code, 'mensaje' => $mensaje));
    }

    public function ajax_confirmar_agregar_bloqueo_dia_completo()
    {
        $timetmp = strtotime($_GET['fechainicio']);
        $start = $timetmp * 1000;
        $timetmp = strtotime($_GET['fechafinal']);
        $end = $timetmp * 1000;
        $data['start'] = $start;
        $data['end'] = $end;
        $data['id'] = $_GET['agenda'];
        $data['bloqueardia'] = true;
        $this->load->view('agenda/ajax_confirmar_agregar_bloqueo_dia', $data);
    }

    public function ajax_obtener_agenda($idagenda)
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        try {
            $uri = $this->base_services . '' . $this->context . 'calendars/' . $idagenda;//url del servicio con los parametros
            Log::debug('ajax_obtener_agenda URI ' . $uri);
            $response = RequestHttp::get($uri)->sendIt();
            Log::debug('ajax_obtener_agenda Response ' . $response);
            $code = $response->code;
            if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                $code = $response->body[0]->response->code;
                $data = $response->body[1]->calendars[0]->owner_name;

            }
        } catch (Exception $err) {
            Log::error('ajax_obtener_agenda ' . $err);
        }
        return response()->json(array('code' => $code, 'mensaje' => $mensaje, 'calendario_owner' => $data));
    }

    public function ajax_mi_calendario($owner)
    {
        $code = 0;
        $mensaje = '';
        $data = array();
        if ($owner > 0) {
            try {
                $uri = $this->base_services . '' . $this->context . 'calendars/listByOwner/' . $owner;//url del servicio con los parametros
                Log::debug('ajax_mi_calendario URI ' . $uri);
                $response = RequestHttp::get($uri)->sendIt();
                Log::debug('ajax_mi_calendario Response ' . $response);
                $code = $response->code;
                if (isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code) && $response->body[0]->response->code == 200) {
                    $code = $response->body[0]->response->code;
                    $mensaje = $response->body[0]->response->message;
                    foreach ($response->body[1]->calendars as $items) {
                        $tmp = new \stdClass();
                        $tmp->id = $items->id;
                        $tmp->name = $items->name;
                        $tmp->owner_id = $items->owner_id;
                        $tmp->owner_name = $items->owner_name;
                        $tmp->owner_email = $items->owner_email;
                        $tmp->is_group = $items->is_group;
                        $tmp->schedule = $items->schedule;
                        $tmp->time_attention = $items->time_attention;
                        $tmp->concurrency = $items->concurrency;
                        $tmp->ignore_non_working_days = $items->ignore_non_working_days;
                        $tmp->time_cancel_appointment = $items->time_cancel_appointment;
                        $tmp->time_confirm_appointment = $items->time_confirm_appointment;
                        $data[] = $tmp;
                    }
                } else {
                    $mensaje = $response->body->response->message;
                }
            } catch (Exception $err) {
                Log::error('ajax_mi_calendario ' . $err);
                $mensaje = $err->getMessage();
            }
            $usuario = Doctrine::getTable('Usuario')->findByid($owner);
            foreach ($usuario[0]->GruposUsuarios as $g) {
                try {
                    $uri = $this->base_services . '' . $this->context . 'calendars/listByOwner/' . $g->id;
                    Log::debug('ajax_mi_calendario URI ' . $uri);
                    $response = RequestHttp::get($uri)->sendIt();
                    Log::debug('ajax_mi_calendario Response ' . $response);
                    $code = $response->code;
                    if (isset($response->code) && $response->code == 200 && isset($response->body) && is_array($response->body) && isset($response->body[0]->response->code)) {
                        foreach ($response->body[1]->calendars as $item) {
                            $tmp = new \stdClass();
                            $tmp->id = $item->id;
                            $tmp->name = $item->name;
                            $tmp->owner_id = $item->owner_id;
                            $tmp->owner_name = $item->owner_name;
                            $tmp->owner_email = $item->owner_email;
                            $tmp->is_group = $item->is_group;
                            $tmp->schedule = $item->schedule;
                            $tmp->time_attention = $item->time_attention;
                            $tmp->concurrency = $item->concurrency;
                            $tmp->ignore_non_working_days = $item->ignore_non_working_days;
                            $tmp->time_cancel_appointment = $item->time_cancel_appointment;
                            $tmp->time_confirm_appointment = $item->time_confirm_appointment;
                            $data[] = $tmp;
                        }
                    }
                } catch (Exception $err) {
                    Log::error('ajax_mi_calendario ' . $err);
                    throw new Exception($err->getMessage());
                }
            }
        }
        echo json_encode(array('code' => $code, 'message' => $mensaje, 'calendars' => $data));
    }


}
