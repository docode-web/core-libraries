<?php 
// var_format 

use Carbon\Carbon;

Class Tools {


	public static function varFormat($v) // pretty-print var_export 
	{ 
		return (str_replace(array("\n"," ","array"), array("<br>","&nbsp;","&nbsp;<i>array</i>"), var_export($v,true))."<br>"); 
	} 

	public static function trace($die = false)
	{ 
		$bt=debug_backtrace();
		$sp=0;
		$trace="";

		foreach($bt as $k=>$v) 
		{ 
			extract($v); 
			$file=substr($file,1+strrpos($file,"/")); 
			if ($file=="db.php")continue; // the db object 
			$trace.=str_repeat("&nbsp;",++$sp); //spaces(++$sp); 
			$trace.="file=$file, line=$line, function=$function<br>";        
		} 

		echo "$trace";

		if ($die) die;
	} 

	public static function dd($x, $die = true)
	{
		if (is_array($x))
		{
			echo "<pre>";
			print_r($x);
			echo "</pre>";
		}
		else
		{
			echo '<pre>'.var_dump($x).'</pre>';
		}

		if($die) die;
	}

	public static function queryLog()
	{
		dd( DB::getQueryLog() );

		/// from Taylor: DB::listen(function($sql) { var_dump($sql); });
	}

	public static function string2Date($date)
	{
		if (!isset($date) or !$date) return null;

		$d = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');

		return "$d";
	}

	public static function date2String($date)
	{
		if (!isset($date) or !$date) return null;

		$d = Carbon::createFromFormat('Y-m-d', $date)->format('d-m-Y');

		return "$d";
	}

	public static function arrayValueReplace($maybe_array, $replace_from, $replace_to) {

		if (!empty($maybe_array)) {
			if (is_array($maybe_array)) {
				foreach ($maybe_array as $key => $value) {
					$maybe_array[$key] = self::arrayValueReplace($value, $replace_from, $replace_to);
				}
			} else {
				if (is_string($maybe_array)){
					$maybe_array = str_replace($replace_from, $replace_to, $maybe_array);
				}               
			}
		}

		return $maybe_array;
	}


	public static function  strBaseConvert($str, $frombase=10, $tobase=36) { 
		$str = trim($str); 
		if (intval($frombase) != 10) { 
			$len = strlen($str); 
			$q = 0; 
			for ($i=0; $i<$len; $i++) { 
				$r = base_convert($str[$i], $frombase, 10); 
				$q = bcadd(bcmul($q, $frombase), $r); 
			} 
		} 
		else $q = $str; 
	  
		if (intval($tobase) != 10) { 
			$s = ''; 
			while (bccomp($q, '0', 0) > 0) { 
				$r = intval(bcmod($q, $tobase)); 
				$s = base_convert($r, 10, $tobase) . $s; 
				$q = bcdiv($q, $tobase, 0); 
			} 
		} 
		else $s = $q; 
	  
		return $s; 
	}     

	public static function toBase36($base10)
	{
		return strtolower(strBaseConvert($base10, 10, 36));
	}

	public static function toBase10($base36)
	{
		return strBaseConvert($base36, 36, 10);
	}    

	public static function formatMoney($value)
	{
		return 'R$ '.static::formatDecimalBR($value, 2);
	}    

	public static function formatDecimalBR($value, $decimals = 2)
	{
		return number_format ( $value , $decimals, ',' , '.' );
	}

	public static function formatDecimal($value, $decimals = 2)
	{
		return number_format ( $value , $decimals, '.' , ',' );
	}

	public static function recursiveUnset(&$array, $unwanted_key) {
		if (!is_array($array) || empty($unwanted_key)) 
			 return false;

		unset($array[$unwanted_key]);

		foreach ($array as &$value) {
			if (is_array($value)) {
				static::recursiveUnset($value, $unwanted_key);
			}
		}
	}

	public static function XML2Array ( $xml , $recursive = false )
	{
		if ( ! $recursive )
		{
			$array = simplexml_load_string ( $xml ) ;
		}
		else
		{
			$array = $xml ;
		}
		
		$newArray = array () ;
		$array = ( array ) $array ;
		foreach ( $array as $key => $value )
		{
			$value = ( array ) $value ;
			if ( isset ( $value [ 0 ] ) )
			{
				$newArray [ $key ] = trim ( $value [ 0 ] ) ;
			}
			else
			{
				$newArray [ $key ] = static::XML2Array ( $value , true ) ;
			}
		}
		return $newArray ;
	}

	static public function date($date) {
		if($date)
		{
			$date = new ExpressiveDate($date);
			$date->setDefaultDateFormat('d.m.Y');
			return "$date - ".static::dayOfWeek($date);
		}
	}

	static public function time($date) {
		if($date)
		{
			$d = new ExpressiveDate($date);
			$d->setDefaultDateFormat('H:i');
			return "$d";
		}
	}

	static public function format($date, $format) {
		if($date)
		{
			$d = new ExpressiveDate($date);
			$d->setDefaultDateFormat($format);
			return Tools::translate("$d");
		}
	}

	static public function dateAndTime($date) {
		return Tools::date($date).' - '.Tools::time($date);
	}

	static public function dayOfWeek($date) {
		if($date)
		{
			$date = new ExpressiveDate($date);
			$date->setDefaultDateFormat('H:i');
			$dow = $date->getDayOfWeek();

			switch($dow)
			{
				case "Monday":    $day = "Segunda";  break;
				case "Tuesday":   $day = "Terça"; break;
				case "Wednesday": $day = "Quarta";  break;
				case "Thursday":  $day = "Quinta"; break;
				case "Friday":    $day = "Sexta";  break;
				case "Saturday":  $day = "Sábado";  break;
				case "Sunday":    $day = "Domingo";  break;
				default:          $day = "erro"; break;
			}			

			return $day;
		}

	}

	static public function diff($date1,$date2) {
		return Tools::seconds2human(Tools::diffInSeconds($date1,$date2));
	}

	static public function diffInSeconds($date1,$date2) {
		if($date1)
		{
			$date1 = new ExpressiveDate($date1);
			if($date2) {
				$date2 = new ExpressiveDate($date2);
			} else {
				$date2 = new ExpressiveDate;
			}
			// $date = 
			//$date->setDefaultDateFormat('H:m:s');
			return $date1->getDifferenceInSeconds($date2);
		}
	}

	static public function seconds2human($ss) {
		$s = $ss%60;
		$m = floor(($ss%3600)/60);
		$h = floor(($ss%86400)/3600);
		$d = floor(($ss%2592000)/86400);
		$M = floor($ss/2592000);

		$r = ($M ? "$M mes".($M>1?"es":"").", " : "")
			.($d ? "$d dia".($d>1?"s":"").", " : "")
			.($h ? $h."h, " : "")
			.($m ? $m."m" : "");

		return $r;			
	}

	static public function seconds2humanHours($ss) {
		$s = $ss%60;
		$m = floor(($ss%3600)/60);
		$h = floor(($ss%86400)/3600);
		$d = floor(($ss%2592000)/86400);
		$M = floor($ss/2592000);

		$h += $d*24;
		$d = 0;

		$r = ($M ? "$M mes".($M>1?"es":"").", " : "")
			.($d ? "$d dia".($d>1?"s":"").", " : "")
			.($h ? $h."h, " : "")
			.($m ? $m."m" : "");

		return $r;			
	}

	static public function firstDayOfWeek($wk_num, $yr, $first = 1, $format = 'F d, Y') 
	{ 
		$wk_ts  = strtotime('+' . $wk_num . ' weeks', strtotime($yr . '0101')); 
		$mon_ts = strtotime('-' . date('w', $wk_ts) + $first . ' days', $wk_ts);

		return $mon_ts; 
	} 

	static public function firstDayOfWeekfromDate($wk_ts)
	{ 
		$mon_ts = strtotime('-' . date('w', $wk_ts) + 1 . ' days', $wk_ts);

		return $mon_ts; 
	} 

	static public function getDay($date) 
	{ 
		if($date)
		{
			$date = new Carbon($date);
			return $date->day;
		}
	} 

	static function translate($s) 
	{

		$s = str_replace('January', 'Janeiro', $s);
		$s = str_replace('February', 'Fevereiro', $s);
		$s = str_replace('March', 'Março', $s);
		$s = str_replace('April', 'Abril', $s);
		$s = str_replace('May', 'Maio', $s);
		$s = str_replace('June', 'Junho', $s);
		$s = str_replace('July', 'Julho', $s);
		$s = str_replace('August', 'Augosto', $s);
		$s = str_replace('September', 'Setembro', $s);
		$s = str_replace('October', 'Outubro', $s);
		$s = str_replace('November', 'Novembro', $s);
		$s = str_replace('December', 'Dezembro', $s);

		return $s;
	}

	static function inIpRange($ip_one, $ip_two=false){ 
		if($ip_two===false){ 
			if($ip_one==$_SERVER['REMOTE_ADDR']){ 
				$ip=true; 
			}else{ 
				$ip=false; 
			} 
		}else{ 
			if(ip2long($ip_one)<=ip2long($_SERVER['REMOTE_ADDR']) && ip2long($ip_two)>=ip2long($_SERVER['REMOTE_ADDR'])){ 
				$ip=true; 
			}else{ 
				$ip=false; 
			} 
		} 
		return $ip; 
	}

	static function utf8EncodeArray(&$input) {
		if (is_string($input)) {
			$input = utf8_encode($input);
		} else if (is_array($input)) {
			foreach ($input as &$value) {
				static::utf8EncodeArray($value);
			}

			unset($value);
		} else if (is_object($input)) {
			$vars = array_keys(get_object_vars($input));

			foreach ($vars as $var) {
				static::utf8EncodeArray($input->$var);
			}
		}
	}
}
