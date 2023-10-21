<?php

class ThinkClientSync
{
    public function write_data($new_data) {
    	try {
    		$old_data = require $GLOBALS['THINK_CLIENT_CONFIG']['cache_meta_data'];
    		if (!is_array($old_data)) {
    			$old_data = array();
    		}

    		$new_data = array_merge($old_data, $new_data);
    		file_put_contents($GLOBALS['THINK_CLIENT_CONFIG']['cache_meta_data'], '<?php return '.var_export($new_data, true).';', LOCK_EX);

    		unset($old_data, $new_data);
    	} catch (Exception $e) {
    		return false;
    	}
		
		return true;
    }
}