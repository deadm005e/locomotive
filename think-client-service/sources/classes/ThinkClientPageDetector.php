<?php

class ThinkClientPageDetector extends ThinkClient{

    public $parent_category;

    public $active_filters = array();
    public $request_active_filters = array();

    private $filter_names_to_noindex = array('Наличие');

	public $page_types;
	public $service_pages;

	public $currentPageType;

	public $page_number;

	private static $instance = null;

    public static function getInstance($html = false)
    {
        if (self::$instance === null)
        {
            self::$instance = new self($html);
        }
        return self::$instance;
    }
    private function __clone() {}

	function __construct($html)
	{
       	parent::__construct($html);

		if(file_exists(THINK_CLIENT_PAGE_TYPES)){
			$this->page_types = include THINK_CLIENT_PAGE_TYPES;
		}
		if(file_exists(THINK_CLIENT_SERVICE_PAGES))
			$this->service_pages = include THINK_CLIENT_SERVICE_PAGES;

		if(!empty($_REQUEST['active_filters']))
			$this->request_active_filters = $_REQUEST['active_filters'];

        $this->detectPageType();

        $this->parseBreadcrumbs();
	}


	public function detectPageType()
	{
		foreach ($this->page_types as $page_type => $type_comment) {
			$detect_method = 'detect'.$page_type;
			$isPageType = 'is'.$page_type;
			if( 
				( empty($type_comment) && method_exists($this, $detect_method) && $this->$detect_method() ) 
				|| 
				(!empty($type_comment) && strpos($this->html, $type_comment) !== false )  
			){
				$this->$isPageType = true;
				$this->currentPageType = $page_type;
			}
			else{
				$this->$isPageType = false;
			}
		}

	}  

    public function parseBreadcrumbs()
    {
    	if(preg_match('/<div class="breadcrumb"(.*?)>(.*?)<\/div>/s', $this->html, $find_bs_block)){
    		$this->bs_html = $find_bs_block[0];
    		$this->getParentCategory();
        }
    }

    //TODO: брать по урлу крошки данные из статики, если есть
	public function getParentCategory()
	{   
        if (preg_match_all('/<a href="(.*?)"[^>]*>(.*?)<\/a>/s', $this->bs_html, $find_bs)) {
            $this->parent_category = array('link' =>end($find_bs[1]), 'name' => strip_tags(end($find_bs[2])));
        }
	}

	public function getActiveFilters()
	{
    	if(!empty($GLOBALS['THINK_ACTIVE_FILTERS'])){
    		$this->active_filters = $GLOBALS['THINK_ACTIVE_FILTERS'];
    	}elseif(preg_match_all('/<!--active_filter:(.*?):(.*?)-->/', $this->html, $find_filters)){
    		foreach ($find_filters[1] as $key => $name) {
    			$this->active_filters[] = array('name' => $name, 'value' => $find_filters[2][$key]);
    		}
    	}
    }

    public function detectIndexFilters()
    {
		$this->getActiveFilters();

    	if(!empty($this->active_filters)){
    		$unique_filters_array = unique_multidim_array($this->active_filters, 'name');
			if(count($this->active_filters) < 3 && $unique_filters_array == $this->active_filters)
				return true;
    	}

    	return false;
	}

	public function detectNoIndexFilters()
	{
    	if($this->isIndexFilters === false && !empty($this->active_filters) || (!empty($this->request_active_filters) && count($this->active_filters) !== foreach_count($this->request_active_filters)) || $this->checkIfNameToNoindex()){
    		
    		$this->isIndexFilters = false;
			return true;
    	}

    	return false;
	}

	public function checkIfNameToNoindex()
	{
		foreach ($this->active_filters as $filter) {
			if(in_array($filter['name'], $this->filter_names_to_noindex))
				return true;
		}
		return false;
	}

	public function detectPagination()
	{
		if(!empty($_GET['page']) && $_GET['page'] != 1 || preg_match('/page(.*?)$/is', $this->uri, $find_pagination) && is_numeric($find_pagination[1]) && $_GET['page'] = $find_pagination[1])
		{
			$this->page_number = $_GET['page'];
    		return true;
		}

    	return false;
	}

	public function detectSort()
	{
		if(!empty($_GET['sort']) || preg_match('/sort(.*?)$/is', $this->uri, $find_sort))
		{
    		return true;
		}

    	return false;
	}

	public function detectService()
	{
		if(!empty($this->service_pages) && in_array($this->uri, $this->service_pages))
			return true;

    	return false;
	}

}