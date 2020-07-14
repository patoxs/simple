<?php
require_once('accion.php');

use App\Helpers\Doctrine;
use Illuminate\Http\Request;

class AccionEventoAnalytics extends Accion
{

    public function displayForm()
    {
       
        $display  = '<br><label><b>HIT enviante a Google Analytics</b></label><br/>';
        $id_cuenta = Cuenta::cuentaSegunDominio() != 'localhost' ? Cuenta::cuentaSegunDominio()->analytics : '';
        $id_instancia = env('ANALYTICS');
        $cuenta_checked = '';
        $instancia_checked = '';
        if (isset($this->extra->tipo_id_seguimiento) && $this->extra->tipo_id_seguimiento== "id_cuenta"){
            $cuenta_checked = "checked='checked'";
        }
        if (isset($this->extra->tipo_id_seguimiento) && $this->extra->tipo_id_seguimiento== "id_instancia"){
            $instancia_checked = "checked='checked'";
        }

        if(!empty($id_cuenta) && !empty($id_instancia))  {
        $display .='<label>Selecciona el ID a enviar</label><br/>'; 
        $display .= '<div class="form-check form-check-inline" id="id_cuenta">
                    <input class="form-check-input tipo-seguimiento" type="radio" id="id_cuenta" name="extra[tipo_id_seguimiento]" value="id_cuenta" '.$cuenta_checked.'>
                    <label class="form-check-label" for="tipo_id_seguimiento">Cuenta</label><br/>
                    </div><p>';

         $display .=  '<div class="form-check form-check-inline" id="id_instancia">
                    <input class="form-check-input tipo-seguimiento" type="radio" id="id_instancia" name="extra[tipo_id_seguimiento]" value="id_instancia" '.$instancia_checked.'>
                    <label class="form-check-label"  for="tipo_id_seguimiento">Instancia</label>
                    </div><p>';

        $display .= '<input type="hidden" name="extra[id_seguimiento]" id="id_seguimiento" value="'. (isset($this->extra->id_seguimiento) ? $this->extra->id_seguimiento : '') .'"></input>';            
            
        $display .= '<br><label title="Para Chile corresponde: Marca Inicial, Ingreso Solicitud, Marca Final">EventAction <br>  </label>';
        $display .= '<input type="text" class="form-control col-2" id="nombre_marca" name="extra[nombre_marca]" onkeyup=mostrar(this.value) value="' . (isset($this->extra->nombre_marca) ? $this->extra->nombre_marca : '') . '">';            
       
        $display .= '<label title="Para Chile es Categoria Trámite Digital">EventCategory <br>  </label>';
        $display .= '<input type="text" class="form-control col-2" id="categoria" name="extra[categoria]" onkeyup=eventos(this.value) value="' . (isset($this->extra->categoria) ? $this->extra->categoria : '') . '">';
        $display .= '<br><label title="Para Chile es el ID RNT">EventLabel <br> </label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[evento_enviante]"  onkeyup=evento(this.value) value="' . (isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') . '">';
        $display .= '<br><label><b>JSON GA</b></label>';
        $display .= '<span><br>{<br><br>
                  hitType: "event",<br><br>
                  eventAction: <label id="resultado"></label>"'.(isset($this->extra->nombre_marca) ? $this->extra->nombre_marca : '').'", <br><br>
                  eventCategory: <label id="eventos"></label>"'.(isset($this->extra->categoria) ? $this->extra->categoria : '').'",<br><br>
                  eventLabel:  <label id="evento"></label>"'.(isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') .'" <br><br>
                  
                     }</span>';

                     $display.= '<script type="text/javascript">
                      function mostrar(valor)
                      {
                          document.getElementById("resultado").innerHTML=valor;
                      }

                      function eventos(valor)
                      {
                      document.getElementById("eventos").innerHTML=valor;
                      }

                      function evento(valor)
                      {
                          document.getElementById("evento").innerHTML=valor;
                          
                      }
                      </script><br>';

        $display .='
                    <script type="text/javascript">
                        $(document).ready(function(){  
                            $(".tipo-seguimiento").on("click", function () {              
                                let id_seguimiento = "'.$id_instancia.'";
                                
                                if ($(this).val()== "id_cuenta") {
                                    id_seguimiento = "'.$id_cuenta.'";
                                }

                                $("#id_seguimiento").val(id_seguimiento);
                            });
                        });
                    </script>';   

        

                      } elseif (!empty($id_cuenta) && empty($id_instancia)) {
                          $display .='<label>Selecciona el ID a enviar</label><br/>'; 
                          $display .= '<div class="form-check form-check-inline" id="id_cuenta">
                    <input class="form-check-input" type="radio" id="id_cuenta" checked name="extra[id_seguimiento]" value="' .  $id_cuenta . '">
                    <label class="form-check-label" name="extra[id_seguimiento]" checked value="' .  $id_cuenta  . '" for="id_cuenta">Cuenta</label><br/>
                    </div><p>';

                     $display .=  '<div class="form-check form-check-inline" id="id_instancia">
                    <input class="form-check-input" type="radio" id="extra[id_seguimiento]" disabled name="extra[id_seguimiento]" value="'.$id_instancia.'">
                    <label class="form-check-label" name="extra[id_seguimiento]" value="'.$id_instancia.'" for="id_instancia">Instancia</label>
                    </div><p>';

        $display .= '<br><label title="Para Chile corresponde: Marca Inicial, Ingreso Solicitud, Marca Final">EventAction <br>  </label>';
        $display .= '<input type="text" class="form-control col-2" id="nombre_marca" name="extra[nombre_marca]" onkeyup=mostrar(this.value) value="' . (isset($this->extra->nombre_marca) ? $this->extra->nombre_marca : '') . '" />';            
       
        $display .= '<label title="Para Chile es Categoria Trámite Digital">EventCategory  <br> </label>';
        $display .= '<input type="text" class="form-control col-2" id="categoria" name="extra[categoria]" onkeyup=eventos(this.value) value="' . (isset($this->extra->categoria) ? $this->extra->categoria : '') . '" />';
        $display .= '<br><label title="Para Chile es el ID RNT">EventLabel  <br> </label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[evento_enviante]"  onkeyup=evento(this.value) value="' . (isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') . '" />';
        $display .= '<br><label><b>JSON GA</b></label>';

        $display .= '<span><br>{<br><br>
                  hitType: "event",<br><br>
                  eventAction: <label id="resultado"></label>"'.(isset($this->extra->nombre_marca) ? $this->extra->nombre_marca : '').'", <br><br>
                  eventCategory: <label id="eventos"></label>"'.(isset($this->extra->categoria) ? $this->extra->categoria : '').'",<br><br>
                  eventLabel:  <label id="evento"></label>"'.(isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') .'" <br><br>
                  
                     }</span>';

                     $display.= '<script type="text/javascript">
                      function mostrar(valor)
                      {
                          document.getElementById("resultado").innerHTML=valor;
                      }

                      function eventos(valor)
                      {
                      document.getElementById("eventos").innerHTML=valor;
                      }

                      function evento(valor)
                      {
                          document.getElementById("evento").innerHTML=valor;
                          
                      }
                      </script><br>';


                      } elseif (!empty($id_instancia) && empty($id_cuenta)) {
                           $display .=  '<div class="form-check form-check-inline" id="id_instancia">
                    <input class="form-check-input" type="radio" id="extra[id_seguimiento]" checked name="extra[id_seguimiento]" value="'.$id_instancia.'">
                    <label class="form-check-label" name="extra[id_seguimiento]" checked value="'.$id_instancia.'" for="id_instancia">Instancia</label>
                    </div><p>';

                     $display .= '<div class="form-check form-check-inline" id="id_cuenta">
                    <input class="form-check-input" type="radio" id="id_cuenta" disabled name="extra[id_seguimiento]" value="' .  $id_cuenta . '">
                    <label class="form-check-label" name="extra[id_seguimiento]" value="' .  $id_cuenta  . '" for="id_cuenta">Cuenta</label><br/>
                    </div><p>';
            
        $display .= '<br><label title="Para Chile corresponde: Marca Inicial, Ingreso Solicitud, Marca Final">EventAction <br>  </label>';
        $display .= '<input type="text" class="form-control col-2" id="nombre_marca" name="extra[nombre_marca]" onkeyup=mostrar(this.value) value="' . (isset($this->extra->nombre_marca) ? $this->extra->nombre_marca : '') . '" />';            
       
        $display .= '<label title="Para Chile la Categoria Trámite Digital">EventCategory <br>  </label>';
        $display .= '<input type="text" class="form-control col-2" id="categoria" name="extra[categoria]" onkeyup=eventos(this.value) value="' . (isset($this->extra->categoria) ? $this->extra->categoria : '') . '" />';
        $display .= '<br><label title="Para Chile es el ID RNT">EventLabel <br> </label>';
        $display .= '<input type="text" class="form-control col-2" name="extra[evento_enviante]"  onkeyup=evento(this.value) value="' . (isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') . '" />';
        $display .= '<br><label><b>JSON GA</b></label>';

        $display .= '<span><br>{<br><br>
                  hitType: "event",<br><br>
                  eventAction: <label id="resultado"></label>"'.(isset($this->extra->nombre_marca) ? $this->extra->nombre_marca : '').'", <br><br>
                  eventCategory: <label id="eventos"></label>"'.(isset($this->extra->categoria) ? $this->extra->categoria : '').'",<br><br>
                  eventLabel:  <label id="evento"></label>"'.(isset($this->extra->evento_enviante) ? $this->extra->evento_enviante : '') .'" <br><br>
                  
                     }</span>';

                     $display.= '<script type="text/javascript">
                      function mostrar(valor)
                      {
                          document.getElementById("resultado").innerHTML=valor;
                      }

                      function eventos(valor)
                      {
                      document.getElementById("eventos").innerHTML=valor;
                      }

                      function evento(valor)
                      {
                          document.getElementById("evento").innerHTML=valor;
                      }
                      </script><br>';

                      }

                else
                       $display .= '<font color="red"><b>!!!No es posible enviar un evento sin ID de seguimiento Google Analytics!!!</font>';
                    
                
        return $display;
    }

    public function validateForm(Request $request)
    {
        $request->validate([
              'extra.nombre_marca' => 'required'
         
        ]);
    }

    public function ejecutar($tramite_id)
    {
        
       $etapa = $tramite_id;
        //$id_analytics = Cuenta::seo_tags()->analytics;
       $regla = new Regla($this->extra->nombre_marca);
       $nombre_marca = $regla->getExpresionParaOutput($etapa->id);

        $regla = new Regla($this->extra->id_seguimiento);
        $id_seguimiento = $regla->getExpresionParaOutput($etapa->id);

        $regla = new Regla($this->extra->categoria);
        $categoria = $regla->getExpresionParaOutput($etapa->id);
        $evento_enviante = null;
        
        if (isset($this->extra->evento_enviante)) {
            $regla = new Regla($this->extra->evento_enviante);
            $evento_enviante = $regla->getExpresionParaOutput($etapa->id);

        }
        
    }

}


