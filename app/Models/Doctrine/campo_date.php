<?php
require_once('campo.php');

use Illuminate\Http\Request;
use App\Helpers\Doctrine;


class CampoDate extends Campo
{
    public $requiere_datos = false;

    protected function display($modo, $dato, $etapa_id = false)
    {
        if ($etapa_id) {
            $etapa = Doctrine::getTable('Etapa')->find($etapa_id);
            $regla = new Regla($this->valor_default);
            $valor_default = $regla->getExpresionParaOutput($etapa->id);
        } else {
            $valor_default = $this->valor_default;
        }

        $display = '<div class="form-group">';
        $display .= '<label class="control-label" for="' . $this->id . '">' . $this->etiqueta . (!in_array('required', $this->validacion) ? ' (Opcional)' : '') . '</label>';
        $display .= '<input id="' . $this->id . '" class="datetimepicker form-control" ' . ($modo == 'visualizacion' ? 'readonly' : '') . ' type="text" name="' . $this->nombre . '" value="' . ($dato && $dato->valor ? $dato->valor : ($valor_default ? $valor_default : '')) . '" placeholder="dd-mm-aaaa" />';

        if ($this->ayuda) {
            $display .= '<span class="help-block">' . $this->ayuda . '</span>';
        }
        $display .= '</div>';

        $optionStart = isset($this->extra->config_date->start->option) ? $this->extra->config_date->start->option : null;
        $optionEnd = isset($this->extra->config_date->end->option) ? $this->extra->config_date->end->option : null;

        $minDate = null;
        $maxDate = null;

        switch ($optionStart) {
            case 'current_date':
                $minDate = date('Y-m-d');
                break;
            case 'start_date':
                $minDate = date("Y-m-d", strtotime($this->extra->config_date->start->date));
                break;
        }

        switch ($optionEnd) {
            case 'current_date':
                $maxDate = date('Y-m-d');
                break;
            case 'end_date':
                $maxDate = date("Y-m-d", strtotime($this->extra->config_date->end->date));
                break;
        }

        $display .= "
                    <script>
                        $(document).ready(function(){
                            const maxDate = \"$maxDate\";
                            const minDate = \"$minDate\";
                            const idDateTime = \"$this->id\";
                            let config = {
                                format: 'DD-MM-YYYY',
                                useCurrent: false,
                                icons: {
                                    previous: 'glyphicon glyphicon-chevron-left',
                                    next: 'glyphicon glyphicon-chevron-right'
                                },
                                locale: 'es'
                            };

                            $('#'+idDateTime).datetimepicker(config).on('dp.show', function(event) {
                                if (minDate) {
                                    $('#'+idDateTime).data('DateTimePicker').minDate(moment(minDate));
                                }
                                if (maxDate) {
                                    $('#'+idDateTime).data('DateTimePicker').maxDate(moment(maxDate));
                                }
                                if (minDate == maxDate) {
                                    $('#'+idDateTime).data('DateTimePicker').defaultDate(moment(minDate));
                                }
                            }).on('dp.error', function(e) {
                                console.error(e);
                                if (moment(e.date._d).format('YYYY-MM-DD') == maxDate) {
                                    let fecha = String(moment(e.date._d).format('YYYY-MM-DD'));
                                    $('#'+idDateTime).data('DateTimePicker').defaultDate(moment(fecha));
                                }
                            });
                         });
                    </script>
                ";
        return $display;
    }

    public function backendExtraFields()
    {
        $optionStart = isset($this->extra->config_date->start->option) ? $this->extra->config_date->start->option : null;
        $dateStart = ($optionStart == 'start_date') ? $this->extra->config_date->start->date : null;
        $numberDayStart = ($optionStart == 'number_of_days') ? $this->extra->config_date->start->number_days : null;

        $optionEnd = isset($this->extra->config_date->end->option) ? $this->extra->config_date->end->option : null;
        $dateEnd = ($optionEnd == 'end_date') ? $this->extra->config_date->end->date : null;
        $numberDayEnd = ($optionEnd == 'number_of_days') ? $this->extra->config_date->end->number_days : null;

        $html = '<div class="row">
            <div class="col-12">
                <br>
                <p>Puedes establecer un rango configurable que te permitirá restringir la fecha que puede ser ingresada (Opcional)</p>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label>Rango Inicial</label>
                    <select class="form-control" id="select-fecha-inicial"
                    name="extra[config_date][start][option]">
                      <option value="no_restrictcion" ' . ($optionStart == 'no_restrictcion' ? 'selected' : '') . '>Sin restricción inicial</option>
                      <option value="current_date" ' . ($optionStart == 'current_date' ? 'selected' : '') . '>No permitir el ingreso de fechas previas al día en curso</option>
                      <option value="start_date" ' . ($optionStart == 'start_date' ? 'selected' : '') . '>Establecer una fecha como punto de partida</option>
                    </select>
                </div>
                <div class="form-group start-secret-inputs ' . ($dateStart == null ? 'd-none' : '') . '" id="input-start-date-content">
                    <input class="form-control datetimepicker" id="input-start-date" type="text"
                    placeholder="Seleccione fecha inicial" name="extra[config_date][start][date]"
                    value="' . ($dateStart != null ? $dateStart : '') . '">
                </div>
                <div class="form-group start-secret-inputs ' . ($numberDayStart == null ? 'd-none' : '') . '" id="num-dias-start-content">
                    <input class="form-control" id="num-dias-start" type="number"
                    name="extra[config_date][start][number_days]" placeholder="Ingrese n° de días" min="1"
                    value="' . ($numberDayStart != null ? $numberDayStart : '1') . '">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label>Rango final</label>
                    <select class="form-control" id="select-fecha-final" name="extra[config_date][end][option]">
                      <option value="no_restrictcion" ' . ($optionEnd == 'no_restrictcion' ? 'selected' : '') . '>Sin restricción</option>
                      <option value="current_date" ' . ($optionEnd == 'current_date' ? 'selected' : '') . '>No permitir el ingreso de fechas posteriores al día en curso</option>
                      <option value="end_date" ' . ($optionEnd == 'end_date' ? 'selected' : '') . '>Establecer una fecha como limite final</option>
                    </select>
                </div>
                <div class="form-group end-secret-inputs ' . ($dateEnd == null ? 'd-none' : '') . '" id="input-end-date-content">
                    <input class="form-control datetimepicker" id="input-end-date" type="text"
                    placeholder="Seleccione fecha final" name="extra[config_date][end][date]"
                    value="' . ($dateEnd != null ? $dateEnd : '') . '">
                </div>
                <div class="form-group end-secret-inputs ' . ($numberDayEnd == null ? 'd-none' : '') . '" id="num-dias-end-content">
                    <input class="form-control" id="num-dias-end" type="number"
                    name="extra[config_date][end][number_days]" placeholder="Ingrese n° de días" min="1"
                    value="' . ($numberDayEnd != null ? $numberDayEnd : '1') . '">
                </div>
            </div>
        </div>';

        $html .= '<script>
                        $(document).ready(function() {
                            var select_start = null;
                            var select_end = null;

                            $(".datetimepicker").datetimepicker({
                                format: "DD-MM-YYYY",
                                icons: {
                                    previous: "glyphicon glyphicon-chevron-left",
                                    next: "glyphicon glyphicon-chevron-right"
                                },
                                locale: "es"
                            });
                            $("#select-fecha-inicial").on("change", function() {
                                select_start = $(this).val();
                                switch ($(this).val()) {
                                  case "start_date":
                                      $("#input-start-date-content").removeClass("d-none");
                                      break;
                                  default:
                                      $("#input-start-date-content").addClass("d-none");
                                      break;
                                }
                            });
                            $("#select-fecha-final").on("change", function() {
                                select_end = $(this).val();
                                switch ($(this).val()) {
                                  case "end_date":
                                      $("#input-end-date-content").removeClass("d-none");
                                      break;
                                  default:
                                      $("#input-end-date-content").addClass("d-none");
                                      break;
                                }
                            });
                        });
                    </script>';

        return $html;
    }

    public function backendExtraValidate(Request $request)
    {
        $extraMetadata = $request->get('extra');
        if (isset($extraMetadata['config_date'])) {
            $request->validate(['extra.config_date' => [function ($attribute, $value, $fail) {

                $startRange = $value['start'];
                $endRange = $value['end'];
                $considerWorkingDays = isset($value['consider_working_days']) ? true:false;

                switch ($startRange['option']) {
                    case 'current_date':
                        if ($endRange['option'] == 'end_date') {
                            $fechaActual = strtotime(date('d-m-Y'));
                            $fechaFinal = strtotime($endRange['date']);

                            if($fechaFinal < $fechaActual) {
                                $fail('El rango final de una fecha no puede ser menor al día de hoy');
                            }
                        }
                        break;
                    case 'start_date':
                        if (!$startRange['date']) {
                            $fail('Para establecer un rango inicial previamente debes seleccionar una fecha');
                        } else if ($endRange['option'] == 'end_date' && $endRange['date'] != null) {
                            $fechaInicial = strtotime($startRange['date']);
                            $fechaFinal = strtotime($endRange['date']);

                            if($fechaFinal < $fechaInicial) {
                                $fail('El rango inicial de una fecha no puede ser mayor al rango final');
                            }
                        }
                        break;
                }

                switch ($endRange['option']) {
                    case 'current_date':
                        if ($startRange['option'] == 'start_date') {
                            $fechaActual = strtotime(date('d-m-Y'));
                            $fechaInicial = strtotime($startRange['date']);

                            if($fechaInicial > $fechaActual) {
                                $fail('El rango inicial de una fecha no puede ser mayor al día de hoy');
                            }
                        }
                        break;
                    case 'end_date':
                        if (!$endRange['date']) {
                            $fail('Para establecer un rango final previamente debes seleccionar una fecha');
                        } else if ($startRange['option'] == 'start_date' && $startRange['date'] != null) {
                            $fechaInicial = strtotime($startRange['date']);
                            $fechaFinal = strtotime($endRange['date']);

                            if($fechaFinal < $fechaInicial) {
                                $fail('El rango final de una fecha no puede ser menor al rango inicial');
                            }
                        }
                        break;
                }
            }]]);
        }
    }

    public function formValidate(Request $request, $etapa_id = null)
    {
        $request->validate([
            $this->nombre => implode('|', array_merge(array('date_prep'), $this->validacion))
        ]);
    }
}