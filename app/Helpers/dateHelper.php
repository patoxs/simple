<?php
namespace App\Helpers;

class dateHelper{

    // $date is (YYYY-mm-dd)
    function add_working_days($date, $days)
    {
        $days = intval($days);
        $date = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $timestamp = strtotime($date);
        $skipdays = array("Saturday", "Sunday");
        $skipdates = array();
        $dia = \Carbon\Carbon::parse($date)->day;
        $mes = \Carbon\Carbon::parse($date)->month;
        $anio = \Carbon\Carbon::parse($date)->year;
        $feriados = $this->getFeriados($anio,$mes);
        if(empty($feriados))
            $feriados = array();
        $i = 1;
        while ($days >= $i) {
            $timestamp = strtotime("+1 day", $timestamp);
            if (in_array(date("l", $timestamp), $skipdays)) {
                $days++;
            } else if (in_array(date("Y-m-d", $timestamp), $feriados)) {
                $days++;
            }
            $i++;
        }
        return date("Y-m-d", $timestamp);
    }

    function get_working_days_count($d1, $d2)
    {
        if ($d1 > $d2) {
            $tmp = $d1;
            $d1 = $d2;
            $d2 = $tmp;
        }

        $fecha_actual = \Carbon\Carbon::createFromFormat("Y-m-d",$d1);
        $mes = \Carbon\Carbon::parse($fecha_actual)->month;
        $anio = \Carbon\Carbon::parse($fecha_actual)->year;

        $feriados = $this->getFeriados($anio,$mes);
        if(empty($feriados))
            $feriados = array();

        $feriados_count = count($feriados);

        $weekend = array("Saturday", "Sunday");
        $working_days = 0;

        $d1 = strtotime($d1);
        $d2 = strtotime($d2);

        for ($d = $d1; date('Y-m-d', $d) <= date('Y-m-d', $d2); $d = strtotime("+1 day", $d)) {
            if (in_array(date('l', $d), $weekend))
                $working_days++;

        }
        return $working_days + $feriados_count;
    }

    function diasHabiles($fecha_vencimiento = null){
        $fecha_actual = \Carbon\Carbon::now('America/Santiago')->format('Y-m-d');
        $dia = \Carbon\Carbon::parse($fecha_actual)->day;
        $mes = \Carbon\Carbon::parse($fecha_actual)->month;
        $anio = \Carbon\Carbon::parse($fecha_actual)->year;
        $feriados = $this->getFeriados($anio,$mes);
        $dias_habiles = $this->getWorkingDays($fecha_actual,$fecha_vencimiento,$feriados);
        return $dias_habiles;
    }

    function getFeriados($anio=null, $mes=null){
        $cacheKey = 'feriados-' . $anio . '-' . $mes;
        $redis = \Redis::connection();
        if(!\Redis::exists($cacheKey)){
            $res = json_decode(file_get_contents('https://apis.digital.gob.cl/fl/feriados/'.$anio.'/'.$mes));
            $feriados = array();
            if (is_array($res)) {
                foreach ($res as $r) {
                    $feriados[] = $r->fecha;
                }
            }
            $feriados = json_encode($feriados);
            $redis->set($cacheKey,$feriados);
        }else{
            $response = $redis->get($cacheKey);
            $feriados = json_decode($response);
        }
        return $feriados;
    }

    function getWorkingDays($startDate,$endDate,$holidays){
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        }
        else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            }
            else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
        //---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0 )
        {
        $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach($holidays as $holiday){
            $time_stamp=strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
    }

    function isWeekend($date) {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0 || $weekDay == 6);
    }

    function diasTotales($fecha_vencimiento = null){
        $fecha_actual = \Carbon\Carbon::today();
        $fecha_vencimiento = \Carbon\Carbon::parse($fecha_vencimiento)->format('Y-m-d H:i:s');
        $dia_actual = \Carbon\Carbon::parse($fecha_actual)->day;
        $dia_vencimiento = \Carbon\Carbon::parse($fecha_vencimiento)->day;
        $valido = true;
        $dias_totales = 0;
        
        if($fecha_actual->greaterThan($fecha_vencimiento)){
            while($valido){
                if($fecha_actual->lessThan($fecha_vencimiento)){
                    $valido = false;
                }else{
                    $fecha_actual = $fecha_actual->subDays(1);
                    $dias_totales--;
                }
            }
        }else{
            while($valido){
                if($fecha_actual->greaterThan($fecha_vencimiento)){
                    $valido = false;
                }else{
                    $fecha_actual = $fecha_actual->addDays(1);
                    $dias_totales++;
                }
            }
        }
        return $dias_totales;
    }
}