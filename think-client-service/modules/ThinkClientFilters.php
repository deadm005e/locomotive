<?php
if(!defined('THINK_CLIENT_INDEX_FILTERS'))
	define('THINK_CLIENT_INDEX_FILTERS',  THINK_CLIENT_ROOT_PATH.'/data/index_filters.php');
/**
 * Модуль для фильтров
 */
class ThinkClientFilters extends ThinkClientHtmlMethods
{
	private $path;
    private $data;

	function __construct($html) 
    {
        parent::__construct($html);
        $this->path = THINK_CLIENT_INDEX_FILTERS;
    }

	public function replace_html()
    {
        if (empty($this->detector) || !$this->validateFile())
            return $this->html;

        if($this->detector->isIndexFilters && !$this->detector->isPagination  && !$this->detector->isSort){
            $this->setRobots('index,follow');
        	$this->saveUrl();
        }

        //noindex filters robots
        if($this->detector->isNoIndexFilters){
            $this->setRobots('noindex,nofollow');
            $this->deleteUrl();
        }

    	return $this->html;
    }

    public function saveUrl()
    {
    	if($this->validateFilterPage()){   
            if(!in_array('https:'.$this->url, $this->data)){
                $this->data[] = 'https:'.$this->url;
                $this->saveData();
            }
    	}
    }

    public function deleteUrl()
    {
        if(($key = array_search('https:'.$this->url, $this->data)) !== false){
            unset($this->data[$key]);
            $this->saveData();
        }
    }

    private function getData()
    {
        $file = fopen($this->path,'rt');
        flock($file,LOCK_SH);
        $data = unserialize(file_get_contents($this->path));
        fclose($file);

        if(!empty($data) || is_array($data)){
            $this->data = $data;
            return true;
        }
        else
            return false;
    }

    private function saveData()
    {
        file_put_contents($this->path, serialize($this->data), LOCK_EX);
    }

	private function validateFile()
    {
    	if(file_exists($this->path) && $this->getData() || file_put_contents($this->path, serialize(array())) && chmod($this->path, 0760) )
			return true;
		else
    		return false;
    }

    private function validateFilterPage()
    {
        // if($this->cache->getData('meta_generation', 'h1'))
            return true;
        // else
            // return false;
    }
}