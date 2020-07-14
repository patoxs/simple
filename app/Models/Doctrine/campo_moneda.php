<?php
require_once('campo.php');
class CampoMoneda extends Campo{
    
    public $requiere_datos=false;
    public static $monedas  = array(
    		
    		'comunes' => array (
    				'CLP' => 'Peso Chileno','PYG' => 'Guaraní','ARS' => 'Argentine peso','BRL' => 'Brazilian real','USD' => 'Dolar Estadounidense','EUR' => 'Euro'
    		),
    		'otras' => array (
    				'AED' => 'United Arab Emirates dirham', 'AFN' => 'Afghan afghani', 'ALL' => 'Albanian lek', 'AMD' => 'Armenian dram', 'AOA' => 'Angolan kwanza', 'ARS' => 'Argentine peso', 'AUD' => 'Australian dollar', 'AWG' => 'Aruban florin', 'AZN' => 'Azerbaijani manat', 'BAM' => 'Bosnia and Herzegovina convertible mark', 'BBD' => 'Barbadian dollar', 'BDT' => 'Bangladeshi taka', 'BGN' => 'Bulgarian lev', 'BHD' => 'Bahraini dinar', 'BIF' => 'Burundian franc', 'BMD' => 'Bermudian dollar', 'BND' => 'Brunei dollar', 'BOB' => 'Bolivian boliviano', 'BRL' => 'Brazilian real', 'BSD' => 'Bahamian dollar', 'BTN' => 'Bhutanese ngultrum', 'BWP' => 'Botswana pula', 'BYR' => 'Belarusian ruble', 'BZD' => 'Belize dollar', 'CAD' => 'Canadian dollar', 'CDF' => 'Congolese franc', 'CHF' => 'Swiss franc', 'CNY' => 'Chinese yuan', 'COP' => 'Colombian peso', 'CRC' => 'Costa Rican colón', 'CUP' => 'Cuban convertible peso', 'CVE' => 'Cape Verdean escudo', 'CZK' => 'Czech koruna', 'DJF' => 'Djiboutian franc', 'DKK' => 'Danish krone', 'DOP' => 'Dominican peso', 'DZD' => 'Algerian dinar', 'EGP' => 'Egyptian pound', 'ERN' => 'Eritrean nakfa', 'ETB' => 'Ethiopian birr', 'FJD' => 'Fijian dollar', 'FKP' => 'Falkland Islands pound', 'GBP' => 'British pound', 'GEL' => 'Georgian lari', 'GHS' => 'Ghana cedi', 'GMD' => 'Gambian dalasi', 'GNF' => 'Guinean franc', 'GTQ' => 'Guatemalan quetzal', 'GYD' => 'Guyanese dollar', 'HKD' => 'Hong Kong dollar', 'HNL' => 'Honduran lempira', 'HRK' => 'Croatian kuna', 'HTG' => 'Haitian gourde', 'HUF' => 'Hungarian forint', 'IDR' => 'Indonesian rupiah', 'ILS' => 'Israeli new shekel', 'IMP' => 'Manx pound', 'INR' => 'Indian rupee', 'IQD' => 'Iraqi dinar', 'IRR' => 'Iranian rial', 'ISK' => 'Icelandic króna', 'JEP' => 'Jersey pound', 'JMD' => 'Jamaican dollar', 'JOD' => 'Jordanian dinar', 'JPY' => 'Japanese yen', 'KES' => 'Kenyan shilling', 'KGS' => 'Kyrgyzstani som', 'KHR' => 'Cambodian riel', 'KMF' => 'Comorian franc', 'KPW' => 'North Korean won', 'KRW' => 'South Korean won', 'KWD' => 'Kuwaiti dinar', 'KYD' => 'Cayman Islands dollar', 'KZT' => 'Kazakhstani tenge', 'LAK' => 'Lao kip', 'LBP' => 'Lebanese pound', 'LKR' => 'Sri Lankan rupee', 'LRD' => 'Liberian dollar', 'LSL' => 'Lesotho loti', 'LTL' => 'Lithuanian litas', 'LVL' => 'Latvian lats', 'LYD' => 'Libyan dinar', 'MAD' => 'Moroccan dirham', 'MDL' => 'Moldovan leu', 'MGA' => 'Malagasy ariary', 'MKD' => 'Macedonian denar', 'MMK' => 'Burmese kyat', 'MNT' => 'Mongolian tögrög', 'MOP' => 'Macanese pataca', 'MRO' => 'Mauritanian ouguiya', 'MUR' => 'Mauritian rupee', 'MVR' => 'Maldivian rufiyaa', 'MWK' => 'Malawian kwacha', 'MXN' => 'Mexican peso', 'MYR' => 'Malaysian ringgit', 'MZN' => 'Mozambican metical', 'NAD' => 'Namibian dollar', 'NGN' => 'Nigerian naira', 'NIO' => 'Nicaraguan córdoba', 'NOK' => 'Norwegian krone', 'NPR' => 'Nepalese rupee', 'NZD' => 'New Zealand dollar', 'OMR' => 'Omani rial', 'PAB' => 'Panamanian balboa', 'PEN' => 'Peruvian nuevo sol', 'PGK' => 'Papua New Guinean kina', 'PHP' => 'Philippine peso', 'PKR' => 'Pakistani rupee', 'PLN' => 'Polish złoty', 'PRB' => 'Transnistrian ruble', 'PYG' => 'Paraguayan guaraní', 'QAR' => 'Qatari riyal', 'RON' => 'Romanian leu', 'RSD' => 'Serbian dinar', 'RUB' => 'Russian ruble', 'RWF' => 'Rwandan franc', 'SAR' => 'Saudi riyal', 'SBD' => 'Solomon Islands dollar', 'SCR' => 'Seychellois rupee', 'SDG' => 'Singapore dollar', 'SEK' => 'Swedish krona', 'SGD' => 'Singapore dollar', 'SHP' => 'Saint Helena pound', 'SLL' => 'Sierra Leonean leone', 'SOS' => 'Somali shilling', 'SRD' => 'Surinamese dollar', 'SSP' => 'South Sudanese pound', 'STD' => 'São Tomé and Príncipe dobra', 'SVC' => 'Salvadoran colón', 'SYP' => 'Syrian pound', 'SZL' => 'Swazi lilangeni', 'THB' => 'Thai baht', 'TJS' => 'Tajikistani somoni', 'TMT' => 'Turkmenistan manat', 'TND' => 'Tunisian dinar', 'TOP' => 'Tongan paʻanga', 'TRY' => 'Turkish lira', 'TTD' => 'Trinidad and Tobago dollar', 'TWD' => 'New Taiwan dollar', 'TZS' => 'Tanzanian shilling', 'UAH' => 'Ukrainian hryvnia', 'UGX' => 'Ugandan shilling', 'UYU' => 'Uruguayan peso', 'UZS' => 'Uzbekistani som', 'VEF' => 'Venezuelan bolívar', 'VND' => 'Vietnamese đồng', 'VUV' => 'Vanuatu vatu', 'WST' => 'Samoan tālā', 'XAF' => 'Central African CFA franc', 'XCD' => 'East Caribbean dollar', 'XOF' => 'West African CFA franc', 'XPF' => 'CFP franc', 'YER' => 'Yemeni rial', 'ZAR' => 'South African rand', 'ZMW' => 'Zambian kwacha','ZWL' => 'Zimbabwean dollar'
    		)
    );
    protected function display($modo, $dato) {
        $display = '<label class="control-label">' . $this->etiqueta . (in_array('required', $this->validacion) ? '' : ' (Opcional)') . '</label>';
        $display.='<div class="controls">';
        $display.='<select id="moneda" class="select-semi-large" name="' . $this->nombre . '" ' . ($modo == 'visualizacion' ? 'readonly' : '') . '>';
        $display.='<option value="">Seleccione moneda</option>';
        $display.='<optgroup label="Comunes">';
        foreach (CampoMoneda::$monedas['comunes'] as $codigo => $moneda){
        	$display.='<option value="'.$codigo.'"'. ($dato && $codigo==$dato->valor ?' selected ':'') .' >'.$moneda.'</option>';
        }
        $display.='</optgroup>';
        $display.='<optgroup label="Otras">';
        foreach (CampoMoneda::$monedas['otras'] as $codigo => $moneda){
        	$display.='<option value="'.$codigo.'"'. ($dato && $codigo==$dato->valor ?' selected ':'') .' >'.$moneda.'</option>';
        }
        $display.='</optgroup>';
        $display.='</select>';
        if($this->ayuda)
            $display.='<span class="help-block">'.$this->ayuda.'</span>';
        $display.='</div>';

        $display .= '
                    <script>
                        $(document).ready(function(){
                            $(".form-control-chosen").chosen();

                            $("#moneda").chosen().change(
            function (evt) {
                var label = $(this.options[this.selectedIndex]).closest("optgroup").prop("label");
        });

                        });
                    </script>
    
                ';
        return $display;
    }
    
    
}