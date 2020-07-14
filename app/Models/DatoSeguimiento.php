<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatoSeguimiento extends Model
{
    protected $table = 'dato_seguimiento';

    public function etapa()
    {
        return $this->belongsTo(Etapa::class);
    }

    static function removeFormatNames($name)
    {
        $utf8_ansi2 = array(
            "\u00c1" =>"Á",
            "\u00c9" =>"É",
            "\u00cd" =>"Í",
            "\u00d1" =>"Ñ",
            "\u00d3" =>"Ó",
            "\u00da" =>"Ú",
            "\u00e1" =>"á",
            "\u00e9" =>"é",
            "\u00ed" =>"í",
            "\u00f1" =>"ñ",
            "\u00f3" =>"ó",
            "\u00fa" =>"ú",
            "\u00fce" => "ü",
            "\u00dc" => "Ü"
        );

        return strtr($name, $utf8_ansi2);
    }

    static function addFormatNames($name)
    {
        $symbols = array("Á", "É", "Í", "Ñ", "Ó", "Ú", "á", "é", "í", "ñ", "ó", "ú");
        $pos = -1;
        $length = strlen($name);

        foreach ($symbols as $symbol) {
            if (strpos($name, $symbol) !== false) {
                $pos = strpos($name, $symbol);
                break;
            }
        }

        if ($pos > -1) {
            if ($pos >= ($length/2)-1) { // por palabras cortas < 5
                $name = substr($name, 0, $pos);
            } else {
                // la letra con tilde la cuenta como 2 letras :/, por eso $pos+2
                $name = substr($name, $pos+2, $length-1);
            }

        }

        return $name;
    }
}
