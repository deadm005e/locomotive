<?php
/**
 * Локальный модуль для генерации текста
 */
class ThinkClientTextGeneration extends ThinkModule
{
    private static $generation_data;
    private static $config;
    private static $current_h2_number = 1;
    private static $tag_data = array();



    function __construct($html) {
        parent::__construct($html);
        if(empty(self::$generation_data))
            self::$generation_data = unserialize(file_get_contents(THINK_CLIENT_DATA_PATH.'/textGeneration/generation_data.php')) ;
        if(empty(self::$config))
            self::$config = include THINK_CLIENT_CONFIGS.'/textGeneration/config.php';
    }

    public function replace_html()
    {
        if($this->validate()){
            $this->generate();
        }
        print_r(self::$generation_data);

        return $this->html;
    }

    protected function validate()
    {
        $this->detector->isProduct = true;
        return ((strpos($this->html, "<!-- text_gen -->") !== false && !empty(self::$generation_data)) 
            && ($this->detector->isProduct || $this->detector->isCategory && !$this->detector->isNoIndexFilters && !$this->detector->isPagination && !$this->detector->isSort )) 
            ? true : false;
        
    }

    protected function generate()
    {
        $generated_text = $this->cache->getData('text_generation');
        $generated_text = null;
        if (!$generated_text){
            $text = '';
            foreach (self::$generation_data['main'] as $key => $value) {
                $first_item_value = $value[0];

                if($result = $this->printTag(trim($first_item_value)))
                    $text .= $result;
                else
                    $text .= trim($value[array_rand($value)]);
                $text .= ' ';
            }
            $generated_text = $text;
            $generated_text = self::editText($generated_text);
            $this->cache->putData($generated_text, 'text_generation');
        }
        if (!empty($generated_text)) {
            $this->html = str_replace('<!-- text_gen -->', $generated_text, $this->html);
        }
    }

    private function printTag($tag){
        $count = 1;
        $results = array();
        if (isset(self::$config['special_tag_count'][$tag]))
            $count = self::$config['special_tag_count'][$tag];
        $i = 1;
        while ($i <= $count) {
            if(!$result = $this->printTagCase($tag)){
                return $result;
            }
            if (in_array($tag, self::$config['tags_to_ucifirst']))
                $result = mb_ucfirst($result);

            $results[] = $result;
            $i++;
        }

        return implode(', ', $results);
    }

    private function printTagCase($tag)
    {
        if($tag == 'page_h1')
            return $this->h1;
        elseif($tag == 'parent_h1')
            return $this->detector->parent_category['name'];
        elseif($tag == '{H2}'){
            if(isset(self::$config['h2'][$this->detector->currentPageType])){
                self::$tag_data[$tag] = self::$config['h2'][$this->detector->currentPageType];
            }
            elseif(isset(self::$config['h2']['default'])){
                self::$tag_data[$tag] = self::$config['h2']['default'];
            }
            else return false;

            self::$tag_data[$tag] = self::$tag_data[$tag][self::$current_h2_number];
            $result = array();
            if($rand_item = self::getRandomTagDataItem($tag, 'heads')){
                $result[] = $rand_item;
            }

            $result[] = $this->h1; 

            if($rand_item = self::getRandomTagDataItem($tag, 'tails'))
                $result[] = $rand_item;

            self::$current_h2_number++;
            return '<h2>'.implode(' ', $result).'</h2>';
        }
        elseif(!empty(self::$generation_data[$tag])){
            if(empty(self::$tag_data[$tag]))
                self::$tag_data[$tag] = self::$generation_data[$tag];

            do{
                $rand_item = self::getRandomTagDataItem($tag);
            }while(!empty($rand_item['url']) && strpos($rand_item['url'], $this->url)  !== false);

            if(!empty($rand_item['url']) && !empty($rand_item['text'])){
                return '<a href="'.trim($rand_item['url']).'">'.trim($rand_item['text']).'</a>';
            }
            elseif(!empty($rand_item['text'])){
                return $rand_item['text'];
            }
        }else{
            return false;
        }
    }

    private static function getRandomTagDataItem($tag, $sub_tag = false)
    {
        if($sub_tag && !empty(self::$tag_data[$tag][$sub_tag])){
            $rand_key = array_rand(self::$tag_data[$tag][$sub_tag]);
            $rand_item = self::$tag_data[$tag][$sub_tag][$rand_key];
            unset(self::$tag_data[$tag][$sub_tag][$rand_key]);
        }elseif(!$sub_tag && !empty(self::$tag_data[$tag])){
            $rand_key = array_rand(self::$tag_data[$tag]);
            $rand_item = self::$tag_data[$tag][$rand_key];
            unset(self::$tag_data[$tag][$rand_key]);
        }
        if(!empty($rand_item))
            return $rand_item;
        else
            return false;
    }

    private static function editText($text){
        $text = str_replace(array(' , ', ' ?', ' .', ' !'), array(', ', '?', '.', '!'), $text);
        return $text;
    }
}
