<?php 
/**
 * Локальный модуль для облака тегов
 */
class ThinkClientTagsCloud extends ThinkModule
{
	static private $tags_data;
	static private $current_tag_type;
	private $current_tags;

	static private $sevice_uris = array(
		'/services/',
		'/rabota-po-biometrii/',
		'/viza-besplatno/',
	);
	static private $vacancy_prefix = '/vakancies/';

	static private $default_tag_type = '{priority}';

   	function __construct($html) {
    	parent::__construct($html);
    	if(empty(self::$tags_data))
            self::$tags_data = unserialize(file_get_contents(THINK_CLIENT_DATA_PATH.'/tagsCloud/tags_cloud_data.php')) ;
        // if(empty(self::$config))
        //     self::$config = include THINK_CLIENT_CONFIGS.'/tags_cloud/config.php';
        if(empty(self::$current_tag_type))
    		$this->getTagType();
    }

    public function replace_html()
    {
        if($this->validate()){
            $this->generate();
        }

        return $this->html;
    }

    protected function validate()
    {
        return ((strpos($this->html, "<!-- tags_cloud -->") !== false && !empty(self::$tags_data)) 
            && ($this->detector->isProduct || $this->detector->isCategory && !$this->detector->isNoIndexFilters && !$this->detector->isPagination && !$this->detector->isSort )) 
            ? true : false;
    }


    protected function generate()
    {	
		if($this->getData())
			$this->print_tags_cloud();
    }

    protected function getData()
    {
    	// $this->current_tags = $this->cache->getData('tags_cloud');
    	if(!$this->current_tags){
    			
			$this->current_tags = array();
			$warning = 1;
			while (!empty(self::$current_tag_type) && count($this->current_tags) < 7 && $warning < 50) {
				$this->getRandomDataItem(self::$current_tag_type);
				$warning++;
				if($warning == 45){
					$warning = 1;
					self::$current_tag_type = self::$default_tag_type;
				}
			}
			
			if(count($this->current_tags) == 7){
				// $this->cache->putData($this->current_tags, 'tags_cloud');
			}
		}

		return !empty($this->current_tags) ? true : false;
    }

    private function getRandomDataItem($tag_type)
    {
    	if(!empty($tag_type) && !empty(self::$tags_data[$tag_type])){
    		$rand_key = array_rand(self::$tags_data[$tag_type]);
			if(self::$tags_data[$tag_type][$rand_key] !== 'https:'.$this->url){
				$this->current_tags[$rand_key] = self::$tags_data[$tag_type][$rand_key];
				unset(self::$tags_data[$tag_type][$rand_key]);
				return true;
			}
    	}else return false;
    }

    private function getTagType()
    {
    	if(in_array($this->uri, self::$sevice_uris))
    		self::$current_tag_type = '{services}';
    	elseif(strpos($this->uri, self::$vacancy_prefix) !== false)
    		self::$current_tag_type = '{vakancies}';
    }

    private function print_tags_cloud()
    {
    	if (empty($this->current_tags))
    		return '';
		$tags_cloud_html = '';
		$tags_cloud_html .= $this->tags_cloud_assets();
		foreach ($this->current_tags as $tag_text => $tag_url) {
			$tags_cloud_items_html[] = '
				<div class="ek-grid__item">
                    <div class="ek-box ek-box_position_relative ek-box_padding-top_s ek-box_padding-bottom_s ek-box_padding-right_l ek-box_padding-left_l ek-box_background_grey ek-box_round_xxs">
                        <a class="ek-link ek-link_wrap_nowrap ek-link_color_blue-500 ek-link_blackhole_full-hover" data-qaid="top-category-link" href="'.$tag_url.'">'.$tag_text.'</a>
                    </div>
                </div>
			';
		}
		$tags_cloud_html .= '
			<div style=" color:#44b71a;" class="container">
            <div class="ek-box ek-box_margin-top_m ek-box_margin-bottom_m ek-box_margin-right_none ek-box_margin-left_none">
                <div class="ek-box ek-box_margin-left_xs ek-box_margin-left_none@large ek-box_margin-right_xs ek-box_margin-right_non@large ek-box_margin-bottom_xs">
                    <h3 class="ek-text ek-text_size_h5 ek-text_weight_bold ek-text_lheight_large">Смотрите также</h3>
                </div>
                <div class="ek-box ek-box_overflow-x_auto ek-box_overflow-y_hidden">
                    <div class="ek-box ek-box_display_inline-block ek-box_padding-left_xs ek-box_padding-left_none@large ek-box_padding-right_xs ek-box_padding-right_non@large">
                        <div class="ek-grid ek-grid_wrap_nowrap ek-grid_wrap_wrap@large ek-grid_indent_xs">
                         '.implode('', $tags_cloud_items_html).'
                        </div>
                    </div>
                </div>
            </div>
        </div>
		';
		$this->html = str_replace('<!--tags_cloud-->', $tags_cloud_html, $this->html);
	}

	private function tags_cloud_assets(){
        return "<style>.lotive_backgrnd::after{background: #f4f4f9;}.lotive_backgrnd{background: #f4f4f9;}.ek-box:after{clear:both}.ek-box:after,.ek-box:before{content:'';display:table}.ek-box_display_inline-block{display:inline-block}.ek-box_position_relative{position:relative}.ek-box_margin-left_none{margin-left:0}.ek-box_margin-right_none{margin-right:0}.ek-box_margin-left_xs{margin-left:10px}.ek-box_margin-right_xs{margin-right:10px}.ek-box_margin-bottom_xs{margin-bottom:10px}.ek-box_margin-top_m{margin-top:20px}.ek-box_margin-bottom_m{margin-bottom:20px}.ek-box_padding-left_xs{padding-left:10px}.ek-box_padding-right_xs{padding-right:10px}.ek-box_padding-top_s{padding-top:15px}.ek-box_padding-bottom_s{padding-bottom:15px}.ek-box_padding-left_l{padding-left:25px}.ek-box_padding-right_l{padding-right:25px}.ek-box_round_xxs{border-radius:2px}.ek-box_overflow-x_auto{overflow-x:auto;-webkit-overflow-scrolling:touch}.ek-box_overflow-y_hidden{overflow-y:hidden;-webkit-overflow-scrolling:touch}.ek-box_background_grey{background:#f4f4f9}.ek-grid{display:flex;flex-wrap:wrap;min-width:0}.ek-grid__item{display:block;box-sizing:border-box;min-width:0;max-width:100%}.ek-grid_indent_xs{margin:calc(10px / 2 * -1)}.ek-grid_indent_xs>.ek-grid__item{padding:calc(10px / 2)}.ek-grid_wrap_nowrap{flex-wrap:nowrap}.ek-link{display:inline;color:#44b71a !important;line-height:1.4em;text-decoration:none}.ek-link_wrap_nowrap{white-space:nowrap}.ek-link_color_blue-500{color:#44b71a}.ek-link_blackhole_full-hover:after{content:'';position:absolute;top:0;left:0;width:100%;height:100%;z-index:10}.ek-text{display:inline;line-height:1.4em}.ek-text_size_h5{font-size:16px}.ek-text_weight_bold{font-weight:700}.ek-text_lheight_large{line-height:1.5em}@media only screen and (min-width:960px){.ek-box_margin-left_none\@large{margin-left:0}.ek-box_padding-left_none\@large{padding-left:0}.ek-grid_wrap_wrap\@large{flex-wrap:wrap}}@media (hover),(min-width:0\\0),(min--moz-device-pixel-ratio:0){.ek-link:hover{cursor:pointer;text-decoration:underline}}:focus:not(:focus-visible){outline:0}h3{font-size:inherit;font-weight:inherit}a{color:#000;cursor:pointer;text-decoration:none}@media only screen and (min-width:960px){div::-webkit-scrollbar{width:7px;height:7px;border-radius:20px;background:rgba(218,218,232,.3)}div::-webkit-scrollbar-thumb{border-radius:20px;background-color:rgba(193,193,212,0)}div:hover::-webkit-scrollbar{background:rgba(218,218,232,.5)}div:hover::-webkit-scrollbar-thumb{background-color:#c1c1d4}}</style>";
    }

}