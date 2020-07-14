<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Descargar</title>
</head>
<body>
 
<div>
  <div style="background-image: url({{ asset('img/reportes/Rectangulo2.png') }}); height:521px;width:512px;top: 40px;z-index: -1; margin:0 auto;">
   <div style="text-indent:40px;line-height:0px">
    <img src="{{ asset('img/reportes/logo_reporte.png') }}"/>
    </div>
    <br style="line-height: 5.1;">
    <h1 style="width: 397px;color: #373737;font-family: Roboto, sans-serif;font-size: 25px;font-weight: bold;text-indent:40px; ">¡Tu reporte está listo! </h1>
    <br>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">Hola, el reporte  <b><?php //echo $nombre_cuenta; ?></b> que has solicitado se encuentra  </div>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> listo para que puedas descargarlo. </div><p>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> Para poder acceder a él, haz click en el siguiente</a>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> enlace: <a href="{{ $link }}">Descargar</a><p>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">Una vez que hagas la descarga del documento, </div>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">no podrás volver a acceder al mismo link.</div>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> Si quieres volver a acceder al mismo reporte, </div>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> deberás generarlo nuevamente. </div><br> 
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;">Saluda atentamente,</div>
    <div style="text-indent:40px;text-align:justify;font-family: Roboto, sans-serif;font-size: 16px;line-height: 24px;"> <b><?php echo $reportname; ?></b></div>
   </p>
	</div>
</div>
</body>
</html>

<style>
.logo-presidencial{
  position:absolute;
  height:6px;
  width:150px;
  top: 40px;
  left : 40px;
  z-index: 1;
}

.rectangulo1
{
  position:absolute;
  height:575px;
  width:620px;
  top: 40px;
  z-index: -1;
}
.center {
  display: block;
  margin-left: auto;
  margin-right: auto;
  width: 50%;
}

p.big {
  line-height: 11.1;
  text-indent:40px;
}

br.salt{
  line-height: 8.1;
}

.titulo
{	
  height: 34px;	
  width: 397px;	
  color: #373737;	
  font-family: "Roboto", sans-serif;	
  font-size: 25px;	
  font-weight: bold;	
  line-height: 10.1;
  text-indent:40px;
}

.text-justificado{
  text-indent:40px;
  text-align:justify;
  font-family: "Roboto", sans-serif;	
  font-size: 16px;	
  line-height: 24px;	

}
</style>