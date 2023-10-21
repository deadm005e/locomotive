<?php

class ThinkClientHtmlMethods extends ThinkClient
{

    public $h1;
	public $title;
    public $meta_desc;

    public $meta_cache_data;
    public $current_static_data;

    public $detector;
    public $cache;


	function __construct($html)
	{
       	parent::__construct($html);


		if(empty($this->meta_cache_data) && file_exists(THINK_META_CACHE_PATH) && is_writable(THINK_META_CACHE_PATH)){
        	$this->meta_cache_data = include THINK_META_CACHE_PATH;
		}
		if (!empty($this->meta_cache_data) && array_key_exists($this->url, $this->meta_cache_data)){
            $this->current_static_data = $this->meta_cache_data[$this->url];
        }
        
		$this->getTitle();
        $this->getH1();
        $this->getMetaDesc();
        if(class_exists('ThinkClientPageDetector'))
        	$this->detector = ThinkClientPageDetector::getInstance($this->html);

        if(class_exists('ThinkClientDataCache'))
        	$this->cache = ThinkClientDataCache::getInstance();

	}
    
    public function getH1()
    {
        $this->h1 = '';

        if (!empty($this->current_static_data['h1']))
            $this->h1 = $this->current_static_data['h1'];
        elseif(preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $this->html, $find_h1))
            $this->h1 = trim($find_h1[1]);

    }


    public function getMetaDesc()
    {
        $this->meta_desc = '';

        if (!empty($this->current_static_data['desc']))
        	$this->meta_desc = $this->current_static_data['desc'];
        elseif(preg_match('#<meta[^>]*name="description"[^>]*content="(.*?)"[^>]*>#is', $this->html, $find_desc))
            $this->meta_desc = $find_desc[1];
    }


    public function getTitle()
    {
        $this->title = '';

        if (!empty($this->current_static_data['title']))
            $this->title = $this->current_static_data['title'];
        elseif (preg_match('#<title([^>]*)>(.*?)</title>#is', $this->html, $find_title))
            $this->title = $find_title[2];

    }

	public function replace_h1($h1)
    {
    	if (false === strpos($GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'], 'utf')
    		&& function_exists('iconv')) {
    		$h1 = iconv('utf-8', $GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'].'//IGNORE', $h1);
    	}

	    $new_h1 = '';
	    if (!empty($h1) && preg_match('#<h1([^>]*)>(.(?!</h1>))*?.?</h1>#is', $this->html, $current_h1_pregs)) {
    		$new_h1 = '<h1'.$current_h1_pregs[1].'>'.$h1.'</h1>';
    		$this->h1 = $h1;
    		$this->html = str_replace($current_h1_pregs[0], $new_h1, $this->html);
	    }

	}

	public function replace_title($title)
	{
		if (false === strpos($GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'], 'utf') && function_exists('iconv')) {
			$title = iconv('utf-8', $GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'].'//IGNORE', $title);
		}

	   	$new_title = '';
		if (!empty($title)) {
			if (preg_match('#<title([^>]*)>(.*?)</title>#is', $this->html, $current_title_pregs)) {
				$new_title = '<title'.$current_title_pregs[1].'>'.$title.'</title>';
				$this->html = str_replace($current_title_pregs[0], $new_title, $this->html);
			} else {
				$new_title = '<title>'.$title.'</title>';
				$this->html = str_replace('</head>', $new_title.'</head>', $this->html);
			}
		}
	}

	public function replace_desc($description)
	{
		if (false === strpos($GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'], 'utf')
				&& function_exists('iconv')) {
				$description = iconv('utf-8', $GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'].'//IGNORE', $description);
		}
		if ($description !== null) {

			$new_meta_description = '<meta name="description" content="'.$description.'" />';

			if (preg_match("#<meta[^>]*name *= *[\"']description[\"'][^>]*content *= *[\"']([^\"']*)[\"'][^>]*>#is", $this->html, $current_description_pregs)) {
				$this->html = str_replace($current_description_pregs[0], $new_meta_description, $this->html);
			} else {
				$this->html = str_replace('</head>', $new_meta_description.'</head>', $this->html);
			}
		}

	}

	public function replace_content($content)
	{
		if (empty($content)) {
			return false;
		}

		if (false === strpos($GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'], 'utf') && function_exists('iconv')) {
			$content = iconv('utf-8', $GLOBALS['THINK_CLIENT_CONFIG']['site_default_encoding'].'//IGNORE', $content);
		}

		if (isset($GLOBALS['THINK_CLIENT_CONFIG']['content_replace']) && !empty($content)) {
			if (preg_match($GLOBALS['THINK_CLIENT_CONFIG']['content_replace'], $this->html, $pregs)) {

				if (4 == sizeof($pregs)) {
					$replace_to = $pregs[1].$content.$pregs[3];
				} else {
					$replace_to = $content;
				}

				$this->html = str_replace($pregs[0], $replace_to, $this->html);
			}
		}
	}
	public function setLastCrumbName($name){
		if(!empty($this->detector->bs_html) && preg_match_all('/<span(.*?)>(.*?)<\/span>/is', $this->detector->bs_html, $find_items)){
			$bs_html = end($find_items[0]);
			$new_bs_html = str_replace(end($find_items[2]), $name, $bs_html);
			$this->html = str_replace($bs_html, $new_bs_html, $this->html);
		}
	}

	public function setRobots($content)
	{
		if (preg_match('#<meta[^>]*?name=[\'\"]?robots[\'\"]?[^>]*?>#si', $this->html)){
			$this->html = preg_replace('#<meta[^>]*?name=[\'\"]?robots[\'\"]?[^>]*?>#si', '<meta name="robots" content="'.$content.'" />', $this->html);
		} else {
			$this->html = str_replace('</head>', '<meta name="robots" content="'.$content.'" />'."\n".'</head>', $this->html);
		}
	}

	public function setCanonical($href)
	{
		if (preg_match('#<link[^>]*?rel=[\'\"]?canonical[\'\"]?[^>]*?>#si', $this->html)){
			$this->html = preg_replace('#<link[^>]*?rel=[\'\"]?canonical[\'\"]?[^>]*?>#si', '<link rel="canonical" href="'.$href.'" />', $this->html);
		} else {
			$this->html = str_replace('</head>', '<link rel="canonical" href="'.$href.'" />'."\n".'</head>', $this->html);
		}
	}
}