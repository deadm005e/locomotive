<?php
/**
 * Модуль для подмены статических данных
 */
class ThinkClientStaticMetas extends ThinkClientHtmlMethods
{
    function __construct($html) {
       parent::__construct($html);
    }

	public function replace_html()
    {   
        if (!empty($this->meta_cache_data) && array_key_exists($this->url, $this->meta_cache_data)){
            foreach ($this->meta_cache_data[$this->url] as $tag => $data) {
                if(!empty($data)){
                    $method = 'replace_'.$tag;
                    $this->$method($data);
                }
            }
        }
        return $this->html;
    }
}