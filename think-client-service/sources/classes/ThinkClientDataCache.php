<?php
//dok
// https://docs.google.com/document/d/1_UvJ93riu5V_SWGV0UWS0sz8JUsLXhYnFhC7xV5i8q8
if(!defined('THINK_CLIENT_CACHE_PATH'))
	define('THINK_CLIENT_CACHE_PATH',  THINK_CLIENT_ROOT_PATH.'/data/cache');
if(!file_exists(THINK_CLIENT_CACHE_PATH))
	mkdir(THINK_CLIENT_CACHE_PATH, 0777, true);

class ThinkClientDataCache {

	private $uri;

	private $path;
	private $full_path;

	private static $data;

	private static $instance = null;
    //Singleton
    public static function getInstance($uri = false)
    {
        if (self::$instance === null)
        {
            self::$instance = new self($uri);
        }
        return self::$instance;
    }
    private function __clone() {}

	private function __construct($uri = false) 
	{
		if (!$uri) 
			$this->uri = $_SERVER['REQUEST_URI'];
		else 
			$this->uri = $uri;
		
		$this->path = THINK_CLIENT_CACHE_PATH;
		$this->generateFilePath();
    }

    private function generateFilePath()
    {
    	if($this->uri === '/'){
			$this->full_path = $this->path.'/index.php';
    	}else{
			$path = trim($this->uri, '/');
			$path_items = explode('/', $path, 3);
			$last_path_item = str_replace('/', '.', end($path_items));
			$last_key = key($path_items);
			unset($path_items[$last_key]);
			$path = implode('/', $path_items);
			$this->path = $this->path.'/'.$path;
			$this->full_path = $this->path.'/'.$last_path_item.'.php';
			// костыль, на случай если в пути есть двойной слеш
			$this->full_path = str_replace('//', '/', $this->full_path);
		}
    }

    private function validateFile()
    {
    	if(file_exists($this->path) || mkdir($this->path, 0777, true)){
    		if(file_exists($this->full_path) || file_put_contents($this->full_path, serialize(array('created_at' => date('d/m/Y H:i:s', time())))))
    			return true;
    	}

		return false;
    }

    public function loadFile()
    {
    	if(empty(self::$data)){
    		if(file_exists($this->full_path))
    			self::$data = unserialize(file_get_contents($this->full_path));
			else{
				self::$data = false;
				return false;
			}
    	}
    	return true;
    }

    private function saveFile()
    {
    	self::$data['updated_at'] = date('d/m/Y H:i:s', time());
		if(file_put_contents($this->full_path, serialize(self::$data)) !== false)
			return true;
		else
			return false;
    }

    public function deleteFile()
    {
    	if (file_exists($this->full_path) && unlink($this->full_path))
    		return true;
		else
			return false;
    }

	public function putData($data_to_save, $data_key, $data_sub_key = null)
	{
		if(!empty($data_to_save) && !empty($data_key) && $this->validateFile() && $this->loadFile() && !empty(self::$data) ){
			if($data_sub_key !== null){
				self::$data[$data_key][$data_sub_key] = $data_to_save;
			}else{
				self::$data[$data_key] = $data_to_save;
			}

			if($this->saveFile()){
				return true;
			}
		}
		
		return false;
	}

	public function getData($data_key, $data_sub_key = null)
	{
    	$this->loadFile();

		if($data_sub_key !== null && isset(self::$data[$data_key][$data_sub_key]))
			return self::$data[$data_key][$data_sub_key]; 
		elseif($data_sub_key == null && isset(self::$data[$data_key]))
			return self::$data[$data_key]; 
		else
			return false;
	}

	public function deleteData($data_key, $data_sub_key = null)
	{
		if($data_sub_key !== null && isset(self::$data[$data_key][$data_sub_key]))
			unset(self::$data[$data_key][$data_sub_key]);
		elseif($data_sub_key == null && isset(self::$data[$data_key]))
			unset(self::$data[$data_key]); 

		if($this->saveFile()){
			return true;
		}
	}

}