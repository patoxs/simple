<?php
require_once('campo.php');

use Illuminate\Http\Request;
use App\Helpers\Doctrine;

class CampoDocumento extends Campo
{

    public $requiere_nombre = true;
    public $requiere_datos = false;
    public $estatico = true;

    function setTableDefinition()
    {
        parent::setTableDefinition();

        $this->hasColumn('readonly', 'bool', 1, array('default' => 1));
    }

    function setUp()
    {
        parent::setUp();
        $this->setTableName("campo");
    }

    public function setReadonly($readonly)
    {
        $this->_set('readonly', 1);
    }

    private function isClientMobile(){
        $ua = \Illuminate\Support\Facades\Request::header('User-Agent');
        $ua = strtolower($ua);

        if(strpos($ua, 'android') !== FALSE){
            return true;
        }else if(strpos($ua, 'ipad') !== FALSE){
            return true;
        }else if(strpos($ua, 'iphone') !== FALSE){
            return true;
        }
        return false;
    }

    protected function display($modo, $dato, $etapa_id = false)
    {
        if (isset($this->extra->firmar) && $this->extra->firmar) {
            return $this->displayFirmador($modo, $dato, $etapa_id);
        } else {
            $display_descarga = $this->displayDescarga($modo, $dato, $etapa_id);
            return $display_descarga;
        }
    }


    private function displayDescarga($modo, $dato, $etapa_id)
    {
        if (!$etapa_id) {
            return '<p><a class="btn btn-success" href="#"><i class="icon-download-alt icon-white"></i> ' . $this->etiqueta . '</a></p>';
        }

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file = $this->Documento->generar($etapa->id);
            if(is_bool($file) && !$file){
                $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                $extra_etapa['error'] = false;
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();
                $ruta = url("/etapas/errores/{$etapa->id}");
                return '<script>window.location.href = "'.$ruta.'";</script>';
            }else{
                $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                $extra_etapa = json_decode($etapa->extra, true);
                if(isset($extra_etapa['error'])){
                    unset($extra_etapa['error']);
                }
                $etapa->extra= json_encode($extra_etapa, true);
                $etapa->save();

                $dato = new DatoSeguimiento();
                $dato->nombre = $this->nombre;
                $dato->valor = $file->filename;
                $dato->etapa_id = $etapa->id;

                $dato->save();
            }

            
        } else {
            $file = Doctrine::getTable('File')->findOneByTipoAndFilename('documento', $dato->valor);
            if ($etapa->pendiente && isset($this->extra->regenerar) && $this->extra->regenerar) {
                if ($file != false) {
                    $file->delete();
                }
                $file = $this->Documento->generar($etapa->id);
                if(is_bool($file) && !$file){
                    $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                    $extra_etapa['error'] = false;
                    $etapa->extra= json_encode($extra_etapa, true);
                    $etapa->save();
                    $ruta = url("/etapas/errores/{$etapa->id}");
                    return '<script>window.location.href = "'.$ruta.'";</script>';
                }else{
                    $etapa = Doctrine::getTable('Etapa')->find($etapa->id);
                    $extra_etapa = json_decode($etapa->extra, true);
                    if(isset($extra_etapa['error'])){
                        unset($extra_etapa['error']);
                    }
                    $etapa->extra= json_encode($extra_etapa, true);
                    $etapa->save();
                    $dato->valor = $file->filename;
                    $dato->save();
                }
                
            }
        }
        $usuario_backend = App\Models\UsuarioBackend::find(Auth::user()->id);
        if($usuario_backend)
            $display = '<p><a class="btn btn-success" target="_blank" href="' . url('documentos/get/0/' . $file->filename.'/'.$usuario_backend->id) . '?id=' . $file->id . '&amp;token=' . $file->llave . '"><i class="icon-download-alt icon-white"></i> ' . $this->etiqueta . '</a></p>';
        else
        $display = '<p><a class="btn btn-success" target="_blank" href="' . url('documentos/get/0/' . $file->filename) . '?id=' . $file->id . '&amp;token=' . $file->llave . '"><i class="icon-download-alt icon-white"></i> ' . $this->etiqueta . '</a></p>';
        if( ! $this->isClientMobile() && isset($this->extra->previsualizacion)){
            $pdf_file = "/documentos/get/1/$file->filename?id=$file->id&token=$file->llave";
            /*
             * Si el ancho y alto son demasiado pequenios, chrome/chromium no muestra el toolbar o el control de zoom
             * Minimo debe ser width 500px x height 275px
             */
            $display .= '<embed src="' . $pdf_file . '" class="document_preview" />';
        }
        return $display;
    }

    private function displayFirmador($modo, $dato, $etapa_id)
    {
        if (!$etapa_id) {
            return '<p>' . $this->etiqueta . '</p>';
        }

        $etapa = Doctrine::getTable('Etapa')->find($etapa_id);

        if (!$dato) {   //Generamos el documento, ya que no se ha generado
            $file = $this->Documento->generar($etapa->id);

            $dato = new DatoSeguimiento();
            $dato->nombre = $this->nombre;
            $dato->valor = $file->filename;
            $dato->etapa_id = $etapa->id;
            $dato->save();
        } else {
            $file = Doctrine::getTable('File')->findOneByTipoAndFilename('documento', $dato->valor);
            if ($etapa->pendiente && isset($this->extra->regenerar) && $this->extra->regenerar) {
                $file->delete();
                $file = $this->Documento->generar($etapa->id);
                $dato->valor = $file->filename;
                $dato->save();
            }
        }

        $display = '<p>' . $this->etiqueta . '</p>';
        $display .= '<div id="exito" class="alert alert-success" style="display: none;">Documento fue firmado con éxito.</div>';
        $display .= '<p><a class="btn btn-info" href="' . site_url('documentos/get/0/' . $dato->valor) . '?id=' . $file->id . '&amp;token=' . $file->llave . '"><i class="icon-search icon-white"></i> Previsualizar el documento</a></p>';


        $isMac = stripos($_SERVER['HTTP_USER_AGENT'], 'macintosh') !== false;

        if ($isMac) {
            $display .= '
        <script>
            function checkPasswordToken(){
                var passwordToken = $("#passwordTokenValue").val();
                        var value = document.SignerApplet.hasPK(passwordToken);
                        if (value === "true") {
                            
                        }
                        else {
                            alert("No se ha detectado Token, por favor inserte su Token de firma o la password ingresada es la incorrecta");
                        }
            }
        </script>
        <div id="password">
            <label>Contraseña del Token:</label> <input id="passwordTokenValue" type="password" />
            <button type="button" class="btn" onclick="checkPasswordToken()">Desbloquear Token</button>
        </div><br />';
        }


        $display .= '
            <script>
                function firmarConToken(){
                    var resultadoApplet = document.SignerApplet.signDocuments();
                    var status=$(resultadoApplet).find("documento").attr("RESULTADO");
                    if (status==="true") {
                        $("#exito").show();
                        $("#password").hide();
                        $("#firmaDiv").hide();
                        alert("Documento firmado con éxito.");     
                    }else{
                        alert("Hubo un error al intentar firmar el documento.");
                    }
                }
                function progreso(tot, eje, documento){
                
                }
            </script>
            <div id="firmaDiv">
            <label>Seleccione la firma</label>      
            <div style="float: left;">
            <applet code="' . ($isMac ? 'cl.agile.pdf.applet.SignerAppletMinSegPressMAC' : 'cl.agile.pdf.applet.SignerAppletMinSegPress') . '" width="350" height="25" name="SignerApplet">
                <param name="jnlp_href" value="' . base_url() . 'assets/applets/signer/' . ($isMac ? 'SignerApplet_0_7_mac.jnlp' : 'SignerApplet_0_9_win.jnlp') . '" />
                <param name="documentosPdf" value="' . htmlspecialchars('<PorFirmar><documento id=\'' . $file->id . '\' token=\'' . $file->llave_firma . '\' comentario=\'Firmado Digitalmente\' lugar=\'Santiago\' tipoFirma=\'TIPO_DOC\'/></PorFirmar>') . '" />
                <param name="urlBaseGet" value="' . site_url('documentos/firma_get') . '" />
                <param name="urlBasePost" value="' . site_url('documentos/firma_post') . '" />
                <param name="cLetra" value="000000" />
                <param name="cFondo" value="FFFFFF" />
            </applet>
            </div>
            <div><button type="button" class="btn btn-success" onclick="firmarConToken()"><i class="icon-pencil icon-white"></i> Firmar Documento</button></div>

        </div>';

        return $display;
    }


    public function backendExtraFields()
    {
        $regenerar = isset($this->extra->regenerar) ? $this->extra->regenerar : null;
        $firmar = isset($this->extra->firmar) ? $this->extra->firmar : null;
        $previsualizacion = isset($this->extra->previsualizacion) ? true : false;

        $html = '<label>Documento</label>';
        $html .= '<select name="documento_id" class="form-control col-4">';
        $html .= '<option value=""></option>';
        foreach ($this->Formulario->Proceso->Documentos as $d)
            $html .= '<option value="' . $d->id . '" ' . ($this->documento_id == $d->id ? 'selected' : '') . '>' . $d->nombre . '</option>';
        $html .= '</select>';

        $html .= '<div class="form-check">
                    <input class="form-check-input" type="radio" name="extra[regenerar]" id="extra_regenerar_0" value="0" ' . (!$regenerar ? 'checked' : '') . ' /> 
                    <label for="extra_regenerar_0" class="form-check-label">El documento se genera solo la primera vez que se visualiza este campo.</label>
                    </div>';
        $html .= '<div class="form-check">
                        <input class="form-check-input" type="radio" name="extra[regenerar]" id="extra_regenerar_1" value="1" ' . ($regenerar ? 'checked' : '') . ' />
                        <label for="extra_regenerar_1" class="form-check-label">El documento se regenera cada vez que se visualiza este campo.</label>
                    </div>';
        $html .= '<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="extra[firmar]" id="checkbox_firmar"  ' . ($firmar ? 'checked' : '') . ' /> 
                    <label for="checkbox_firmar" class="form-check-label">Deseo firmar con token en este paso.</label>
                  </div>';
        $html .= '<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="extra[previsualizacion]" id="checkbox_previsualizacion"  ' . ($previsualizacion ? 'checked' : '') . ' /> 
                    <label for="checkbox_previsualizacion" class="form-check-label">Deseo previsualizar el documento. Solo en navegadores Firefox y Chrome</label>
                  </div>';

        return $html;
    }

    public function backendExtraValidate(Request $request)
    {
        parent::backendExtraValidate($request);

        $request->validate(['documento_id' => 'required']);
    }

}
