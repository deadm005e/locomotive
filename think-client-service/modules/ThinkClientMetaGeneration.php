<?php
/**
 * Модуль для генерации мета
 */
class ThinkClientMetaGeneration extends ThinkClientHtmlMethods
{
    private $formulas_conf;

    function __construct($html) 
    {
        parent::__construct($html);

        if(file_exists(THINK_CLIENT_META_FORMULAS))
            $this->formulas_conf = include_once THINK_CLIENT_META_FORMULAS;
    }

	public function replace_html()
    {   
        if (empty($this->detector) || empty($this->formulas_conf) || empty($this->detector->parent_category))
            return $this->html;

        //mod
        if(!empty($this->detector->active_filters) && !empty($this->detector->parent_category) ){
            $this->replace_h1($this->detector->parent_category['name']);
        }
        //mod

        foreach (array_keys($this->detector->page_types) as $page_type) {
            $isPageType = 'is'.$page_type;
            if($this->detector->$isPageType === true){
                $this->generate($page_type);
            }
        }
       
        return $this->html;
    }

    public function generate($page_type)
    {
        $formulas = $this->formulas_conf[$page_type];

        $formula_data['h1'] = $this->h1;
        $formula_data['parent_category_name'] = $this->detector->parent_category['name'];

        if(!empty($this->detector->page_number))
            $formula_data['page_number'] = $this->detector->page_number;

        if($page_type == 'IndexFilters'){
            $filters_count = count($this->detector->active_filters);
            $h1_formulas = $formulas['h1'][$filters_count];

            $i = 1;
            foreach ($this->detector->active_filters as $active_filter) {
                if(isset($h1_formulas[$active_filter['name']])){
                    $formulas['h1'] = $h1_formulas[$active_filter['name']];
                    $formula_data[$active_filter['name'].'_value'] = $active_filter['value'];
                }else{
                    $formula_data['filter_name_'.$i] = $active_filter['name'];
                    $formula_data['filter_value_'.$i] = $active_filter['value'];
                    $i++;
                }
            }
            if(is_array($formulas['h1']))
                $formulas['h1'] = $h1_formulas['default'];
        }
        $this->triggerFormulas($formulas, $formula_data);
    }

    public function triggerFormulas($formulas, $formula_data)
    {
        foreach ($formulas as $tag => $formula) {
            $replace_tag = 'replace_'.$tag;
            $new_data = $this->prepareData($formula, $formula_data);
            if($new_data !== false){
                if($tag == 'h1')
                    $formula_data['h1'] = $new_data;
                $this->$tag = $new_data;
                
                $this->$replace_tag($new_data);
            }
        }
    }

    private function prepareData($formula, $formula_data)
    {
        if(preg_match_all('/\{(.*?)\}/', $formula, $variables_placeholders)){
            foreach ($variables_placeholders[1] as $key => $placeholder) {
                if(isset($formula_data[$placeholder]))
                    $formula = str_replace($variables_placeholders[0][$key], $formula_data[$placeholder], $formula);
                else
                    return false;
            }
        }

        return $formula;
    }

}