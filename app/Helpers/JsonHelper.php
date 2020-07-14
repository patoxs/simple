<?php

function xml_encode($mixed, $domElement = null, $DOMDocument = null)
{
    if (is_object($mixed))
        $mixed = get_object_vars($mixed);

    if (is_null($DOMDocument)) {
        $DOMDocument = new DOMDocument;
        $DOMDocument->formatOutput = true;
        xml_encode($mixed, $DOMDocument, $DOMDocument);
        echo $DOMDocument->saveXML();
    } else {
        if (is_array($mixed)) {
            foreach ($mixed as $index => $mixedElement) {
                if (is_int($index)) {
                    if ($index == 0) {
                        $node = $domElement;
                    } else {
                        $node = $DOMDocument->createElement($domElement->tagName);
                        $domElement->parentNode->appendChild($node);
                    }
                } else {
                    $plural = $DOMDocument->createElement($index);
                    $domElement->appendChild($plural);
                    $node = $plural;
                    //if(rtrim($index,'s')!==$index){
                    //    $singular=$DOMDocument->createElement(rtrim($index,'s'));
                    //    $plural->appendChild($singular);
                    //    $node=$singular;
                    //}
                }
                xml_encode($mixedElement, $node, $DOMDocument);
            }
        } else {
            $domElement->appendChild($DOMDocument->createTextNode($mixed));
        }
    }
}

/**
 * Indents a flat JSON string to make it more human-readable.
 *
 * @param string $json The original JSON string to process.
 *
 * @return string Indented version of the original JSON string.
 */
function json_indent($json)
{

    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = '    ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;

    for ($i = 0; $i <= $strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
        } else if (($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos--;
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos++;
            }

            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }

        $prevChar = $char;
    }

    return $result;
}

function isJSON($string)
{
    return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}


function getJson()
{

    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            echo ' - Sin errores';
            break;
        case JSON_ERROR_DEPTH:
            throw new Exception(' - Excedido tamaño máximo de la pila', 500);
        case JSON_ERROR_STATE_MISMATCH:
            throw new Exception(' - Desbordamiento de buffer o los modos no coinciden', 500);
        case JSON_ERROR_CTRL_CHAR:
            throw new Exception(' - Encontrado carácter de control no esperado', 500);
        case JSON_ERROR_SYNTAX:
            throw new Exception(' - Error de sintaxis, JSON mal formado', 500);
        case JSON_ERROR_UTF8:
            throw new Exception(' - Error de sintaxis, JSON mal formado', 500);
        default:
            echo ' - Error desconocido';
            break;
    }


}

function tabla_declaracion($json)
{
    $json_encodedd = json_encode($json);
    $resultado = json_decode($json_encodedd);
    $tablas_anidadas = "";

    if (count($resultado->registroPorAnnio) > 1) {
        foreach ($resultado->registroPorAnnio as $registro) {
            $columnas = "";
            $filas = "";
            $mes = 0;
            $panio = '<p>' . $registro->annio . '</p>';
            $tabla = '<table border="1" cellpadding="1" cellspacing="1"><thead>';
            foreach ($registro->registrosPorMeses->mes as $registro2) {
                if ($mes == 0)
                    $columnas .= "<tr>";
                $filas .= "<tr>";
                foreach ($registro2 as $registro3 => $valor3) {
                    if ($mes == 0) {
                        $spaced = preg_replace('/([A-Z])/', ' $1', $registro3);
                        $columnas .= '<td><strong>' . $spaced . '</strong></td>';
                    }
                    $spaced = preg_replace('/([A-Z])/', ' $1', $valor3);
                    $filas .= '<td>' . $valor3 . '</td>';
                }
                if ($mes == 0)
                    $columnas .= "</tr>";
                $filas .= "</tr>";
                $mes++;
            }
            $tabla .= $columnas . '</thead><tbody>' . $filas . '</tbody></table>';
            $tablas_anidadas .= $panio . $tabla;
        }
    } elseif (count($resultado->registroPorAnnio) == 1) {
        $mes = 0;
        if ($resultado->registroPorAnnio->annio != 0) {
            foreach ($resultado->registroPorAnnio->registrosPorMeses->mes as $registro2) {
                $panio = '<p>' . $resultado->registroPorAnnio->annio . '</p>';
                $tabla = '<table border="1" cellpadding="1" cellspacing="1"><thead>';
                if ($mes == 0)
                    $columnas .= "<tr>";
                $filas .= "<tr>";
                foreach ($registro2 as $registro3 => $valor3) {
                    if ($mes == 0) {
                        $spaced = preg_replace('/([A-Z])/', ' $1', $registro3);
                        $columnas .= '<td><strong>' . $spaced . '</strong></td>';
                    }
                    $filas .= '<td>' . $valor3 . '</td>';
                }
                if ($mes == 0)
                    $columnas .= "</tr>";
                $filas .= "</tr>";
                $mes++;
            }
            $tabla .= $columnas . '</thead><tbody>' . $filas . '</tbody></table>';
            $tablas_anidadas .= $panio . $tabla;
        }
    }
    return $tablas_anidadas;
}

function tabla_pension($json)
{

    $json_encodedd = json_encode($json);
    $resultado = json_decode($json_encodedd);
    $tablas_anidadas = "";

    //Haberes
    if (count($resultado->emision->haberes->registrosContables) > 1) {
        $mes = 0;
        $columnas = "";
        $filas = "";
        $tabla_haberes = '<tr><td style="float: left;"><table border="1" cellpadding="1" cellspacing="1"><thead><tr><td colspan="2"><center><strong>Haberes</strong></center></td></tr>';
        foreach ($resultado->emision->haberes->registrosContables as $registro2) {
            if ($mes == 0)
                $columnas .= "<tr>";
            $filas .= "<tr>";
            foreach ($registro2 as $registro3 => $valor3) {
                if ($mes == 0)
                    $columnas .= '<td><strong>' . $registro3 . '</strong></td>';
                $filas .= '<td>' . $valor3 . '</td>';
            }
            if ($mes == 0)
                $columnas .= "</tr>";
            $filas .= "</tr>";
            $mes++;
        }
        $tabla_haberes .= $columnas . '</thead><tbody>' . $filas . '</tbody></table></td>';
    } elseif (count($resultado->emision->haberes->registrosContables) == 1) {
        $mes = 0;
        $columnas = "";
        $filas = "";
        $tabla_haberes = '<tr><td style="float: left;"><table border="1" cellpadding="1" cellspacing="1"><thead><tr><td colspan="2"><center><strong>Haberes</strong></center></td></tr>';
        $columnas .= "<tr>";
        $filas .= "<tr>";
        foreach ($resultado->emision->haberes->registrosContables as $registro3 => $valor3) {
            $columnas .= '<td><strong>' . $registro3 . '</strong></td>';
            $filas .= '<td>' . $valor3 . '</td>';
            $mes++;
        }
        $columnas .= "</tr>";
        $filas .= "</tr>";
        $tabla_haberes .= $columnas . '</thead><tbody>' . $filas . '</tbody></table></td></tr>';
    }
    //Fin Haberes

    //Descuentos
    if (count($resultado->emision->descuentos->registrosContables) > 1) {
        $mes = 0;
        $columnas = "";
        $filas = "";
        $tabla_descuentos = '<td style="float: right;"><table border="1"cellpadding="1" cellspacing="1"><thead><tr><td colspan="2"><center><strong>Descuentos</strong></center></td></tr>';
        foreach ($resultado->emision->descuentos->registrosContables as $registro2) {
            if ($mes == 0)
                $columnas .= "<tr>";
            $filas .= "<tr>";
            foreach ($registro2 as $registro3 => $valor3) {
                if ($mes == 0)
                    $columnas .= '<td><strong>' . $registro3 . '</strong></td>';
                $filas .= '<td>' . $valor3 . '</td>';
            }
            if ($mes == 0)
                $columnas .= "</tr>";
            $filas .= "</tr>";
            $mes++;
        }
        $tabla_descuentos .= $columnas . "</thead><tbody>" . $filas . "</tbody></table></td></tr>";
    } elseif (count($resultado->emision->descuentos->registrosContables) == 1) {
        $columnas = "";
        $filas = "";
        $tabla_descuentos = '<div style="float:left"><table border="1" cellpadding="1" cellspacing="1"><thead><tr><td colspan="2"><center><strong>Descuentos</strong></center></td></tr><tr>';
        foreach ($resultado->emision->descuentos->registrosContables as $registro3 => $valor3) {
            $columnas .= '<td><strong>' . $registro3 . '</strong></td>';
            $filas .= '<td>' . $valor3 . '</td>';
        }
        $columnas .= "</tr>";
        $filas .= "</tr>";
        $tabla_descuentos .= $columnas . '</thead><tbody>' . $filas . '</tbody></table></div>';
    }
    if (!empty($tabla_haberes) || !empty($tabla_descuentos))
        $tablas_anidadas .= '<table style="width: 100%;">' . $tabla_haberes . $tabla_descuentos . '</table>';

    return $tablas_anidadas;
}
