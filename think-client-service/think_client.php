<?php

function think_client_html_replace($html)
{	
	$ThinkClient = ThinkClient::getInstance($html);

	if(!ThinkClient::$done_already)
		$ThinkClient->replaceHtml();

    return $ThinkClient->html;
}

class ThinkClient
{
	public $html;
    public $uri;
	public $url;

    private static $dirname;
    private static $folders = array('sources','modules');
    private static $pathes;

    private static $max_scan_depth = 5;

    private static $instance = null;
    public static $done_already = false;
    //Singleton
    public static function getInstance($html = false)
    {
        if (self::$instance === null)
        {
            self::$instance = new self($html);
            self::$dirname = dirname(__FILE__);
        }
        return self::$instance;
    }
	function __construct($html)
	{
		$this->html = $html;
		$this->uri = $_SERVER['REQUEST_URI'];
		$this->url = "//".$_SERVER['SERVER_NAME'].$this->uri;
	}

	private static function init()
	{
		self::loadConfigs();
		self::loadFolders();
	}

	private static function loadConfigs()
	{
		include_once self::$dirname.'/config.php';
	}

	private static function loadFolders()
	{
		foreach (self::$folders as $folder) {
			self::_include_all(self::$dirname.'/'.$folder);
		}
	}

	protected static function _include_all($dir, $depth=0) 
	{
        if ($depth > self::$max_scan_depth) {
            return;
        }
        $scan = glob("$dir/*");

        foreach ($scan as $path) {
            if (preg_match('/\.php$/', $path)) {
            	self::$pathes[] = $path;
                include_once $path;
            }
            elseif (is_dir($path)) {
                self::_include_all($path, $depth+1);
            }
        }
    }

	public function replaceHtml()
	{
		if ($this->validateRequest()){
			self::init();
			$this->activateModules();
			self::$done_already = true;
		}
	}

	private function activateModules()
	{
		foreach ($GLOBALS['THINK_CLIENT_CONFIG']['modules'] as $module)
		{
			if (!class_exists($module))
				continue;

			$module = new $module($this->html);

		    if (method_exists($module, 'replace_html')) {
		        $this->html = $module->replace_html();
		    }
		}
    	$this->html = str_replace('</body>', '<!-- think_on --></body>', $this->html);
	}

	private function validateRequest()
	{
		$think_exclude_paremetrs = array('wbraid', 'gclid', 'fbclid', 'yclid', 'utm_', '.php');
		$skip_think = false;
		foreach ($think_exclude_paremetrs as $parametr) {
			if(strpos($this->uri, $parametr) !== false){
				$skip_think = true;
			}
		}

		if($skip_think)
			return false;
		else
			return true;	
	}
}

