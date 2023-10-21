<?php

	function unique_multidim_array($array, $key) {
	    $temp_array = array();
	    $i = 0;
	    $key_array = array();
	   
	    foreach($array as $val) {
	        if (!in_array($val[$key], $key_array)) {
	            $key_array[$i] = $val[$key];
	            $temp_array[$i] = $val;
	        }
	        $i++;
	    }
	    return $temp_array;
	}

	function foreach_count($array){
		$i = 0;
		foreach ($array as $value) {
			$i += count($value);
		}

		return $i;
	}

	if(!function_exists('mb_ucfirst')){

		function mb_ucfirst($str, $encoding='UTF-8')
	    {
	        $str = mb_ereg_replace('^[\ ]+', '', $str);
	        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
	               mb_substr($str, 1, mb_strlen($str), $encoding);
	        return $str;
	    }
	}