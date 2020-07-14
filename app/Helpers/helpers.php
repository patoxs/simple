<?php

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

if (!function_exists('prep_url')) {
    /**
     * Prep URL
     *
     * Simply adds the http:// part if no scheme is included
     *
     * https://github.com/bcit-ci/CodeIgniter/blob/master/system/helpers/url_helper.php
     *
     * @param   string  the URL
     * @return  string
     */
    function prep_url($str = '')
    {
        if ($str === 'http://' OR $str === '') {
            return '';
        }
        $url = parse_url($str);
        if (!$url OR !isset($url['scheme'])) {
            return 'http://' . $str;
        }
        return $str;
    }
}


/**
 * Converts a MySQL Timestamp to Unix
 *
 * @access    public
 * @param    integer Unix timestamp
 * @return    integer
 */
if (!function_exists('mysql_to_unix')) {
    /**
     * Converts a MySQL Timestamp to Unix
     *
     * @param    int    MySQL timestamp YYYY-MM-DD HH:MM:SS
     * @return    int    Unix timstamp
     */
    function mysql_to_unix($time = '')
    {
        // We'll remove certain characters for backward compatibility
        // since the formatting changed with MySQL 4.1
        // YYYY-MM-DD HH:MM:SS
        $time = str_replace(array('-', ':', ' '), '', $time);
        // YYYYMMDDHHMMSS
        $hour = substr($time, 8, 2);

        return mktime(
            $hour == '' ? 0 : $hour,
            substr($time, 10, 2),
            substr($time, 12, 2),
            substr($time, 4, 2),
            substr($time, 6, 2),
            substr($time, 0, 4)
        );
    }
}


/**
 * Get Mime by Extension
 *
 * Translates a file extension into a mime type based on config/mimes.php.
 * Returns FALSE if it can't determine the type, or open the mime config file
 *
 * Note: this is NOT an accurate way of determining file mime types, and is here strictly as a convenience
 * It should NOT be trusted, and should certainly NOT be used for security
 *
 * @access    public
 * @param    string    path to file
 * @return    mixed
 */
if (!function_exists('get_mime_by_extension')) {
    function get_mime_by_extension($file)
    {
        $extension = strtolower(substr(strrchr($file, '.'), 1));

        $mimes = array('hqx' => 'application/mac-binhex40',
            'cpt' => 'application/mac-compactpro',
            'csv' => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
            'bin' => 'application/macbinary',
            'dms' => 'application/octet-stream',
            'lha' => 'application/octet-stream',
            'lzh' => 'application/octet-stream',
            'exe' => array('application/octet-stream', 'application/x-msdownload'),
            'class' => 'application/octet-stream',
            'psd' => 'application/x-photoshop',
            'so' => 'application/octet-stream',
            'sea' => 'application/octet-stream',
            'dll' => 'application/octet-stream',
            'oda' => 'application/oda',
            'pdf' => array('application/pdf', 'application/x-download'),
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            'smi' => 'application/smil',
            'smil' => 'application/smil',
            'mif' => 'application/vnd.mif',
            'xls' => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
            'ppt' => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
            'wbxml' => 'application/wbxml',
            'wmlc' => 'application/wmlc',
            'dcr' => 'application/x-director',
            'dir' => 'application/x-director',
            'dxr' => 'application/x-director',
            'dvi' => 'application/x-dvi',
            'gtar' => 'application/x-gtar',
            'gz' => 'application/x-gzip',
            'php' => 'application/x-httpd-php',
            'php4' => 'application/x-httpd-php',
            'php3' => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps' => 'application/x-httpd-php-source',
            'js' => 'application/x-javascript',
            'swf' => 'application/x-shockwave-flash',
            'sit' => 'application/x-stuffit',
            'tar' => 'application/x-tar',
            'tgz' => array('application/x-tar', 'application/x-gzip-compressed'),
            'xhtml' => 'application/xhtml+xml',
            'xht' => 'application/xhtml+xml',
            'zip' => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mpga' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'mp3' => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'ram' => 'audio/x-pn-realaudio',
            'rm' => 'audio/x-pn-realaudio',
            'rpm' => 'audio/x-pn-realaudio-plugin',
            'ra' => 'audio/x-realaudio',
            'rv' => 'video/vnd.rn-realvideo',
            'wav' => array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp' => array('image/bmp', 'image/x-windows-bmp'),
            'gif' => 'image/gif',
            'jpeg' => array('image/jpeg', 'image/pjpeg'),
            'jpg' => array('image/jpeg', 'image/pjpeg'),
            'jpe' => array('image/jpeg', 'image/pjpeg'),
            'png' => array('image/png', 'image/x-png'),
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'css' => 'text/css',
            'html' => 'text/html',
            'htm' => 'text/html',
            'shtml' => 'text/html',
            'txt' => 'text/plain',
            'text' => 'text/plain',
            'log' => array('text/plain', 'text/x-log'),
            'rtx' => 'text/richtext',
            'rtf' => 'text/rtf',
            'xml' => 'text/xml',
            'xsl' => 'text/xml',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'movie' => 'video/x-sgi-movie',
            'doc' => 'application/msword',
            'docx' => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'),
            'xlsx' => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'),
            'word' => array('application/msword', 'application/octet-stream'),
            'xl' => 'application/excel',
            'eml' => 'message/rfc822',
            'json' => array('application/json', 'text/json')
        );

        if (!is_array($mimes)) {
            return FALSE;
        }

        if (array_key_exists($extension, $mimes)) {
            if (is_array($mimes[$extension])) {
                // Multiple mime types, just give the first one
                return current($mimes[$extension]);
            } else {
                return $mimes[$extension];
            }
        } else {
            return FALSE;
        }
    }
}


if (!function_exists('random_string')) {
    function random_string($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'basic'    :
                return mt_rand();
                break;
            case 'alnum'    :
            case 'numeric'    :
            case 'nozero'    :
            case 'alpha'    :

                switch ($type) {
                    case 'alpha'    :
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum'    :
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric'    :
                        $pool = '0123456789';
                        break;
                    case 'nozero'    :
                        $pool = '123456789';
                        break;
                }

                $str = '';
                for ($i = 0; $i < $len; $i++) {
                    $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                }
                return $str;
                break;
            case 'unique'    :
            case 'md5'        :

                return md5(uniqid(mt_rand()));
                break;
            case 'encrypt'    :
            case 'sha1'    :

                $CI =& get_instance();
                $CI->load->helper('security');

                return do_hash(uniqid(mt_rand(), TRUE), 'sha1');
                break;
        }
    }
}

if (!function_exists('get_puntos_rut')) {
    function get_puntos_rut($originalRut, $incluyeDigitoVerificador = true){
        $originalRut = trim($originalRut);
        $originalRut = ltrim($originalRut, '0');
        $guion_pos = strpos($originalRut, '-');
        if(! $incluyeDigitoVerificador && $guion_pos !== FALSE){
            $originalRut = substr($originalRut, 0, $guion_pos);
        }
        $input = str_split($originalRut);
        $cleanedRut = '';

        foreach ($input as $key => $chr) {

            //Digito Verificador
            if ((($key + 1) == count($input)) && ($incluyeDigitoVerificador)){
                if (is_numeric($chr) || ($chr == 'k') || ($chr == 'K'))
                    $cleanedRut .= '-'.strtoupper($chr);
                else
                    return false;
            }

            //Números del RUT
            elseif (is_numeric($chr))
                    $cleanedRut .= $chr;
        }
        
        if (strlen($cleanedRut) < 3)
            return false;
        
        if(strpos($cleanedRut, '-') !== FALSE){
            $rutTmp = explode("-",$cleanedRut);
            return number_format($rutTmp[0], 0, "", ".") . '-' . $rutTmp[1];
        }
        
        return number_format($cleanedRut, 0, "", ".");
    }
}
if (!function_exists('get_edad')) {
    function get_edad($date_from_str, $format_str='d-m-Y', $time_zone='America/Santiago'){
        try{
            $tz  = new DateTimeZone($time_zone);
        }catch(\Exception $err){
            return 'Zona horaria invalida: '.$time_zone;
        }
        $date = DateTime::createFromFormat($format_str, $date_from_str, $tz);
        if($date === FALSE){
            return 'Formato de fecha invalida: '.$format_str;
        }
        $age = $date->diff(new DateTime('now', $tz))->y;
        return $age;
    }
}
