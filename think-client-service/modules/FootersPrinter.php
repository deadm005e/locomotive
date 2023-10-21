<?php

class FootersPrinter {
    private $footers_cache_data;
    
    private $footers_items = array(
        'top_requests' => 'ТОП запросы',
        'top_menu' => 'ТОП меню',
        'top_cards' => 'ТОП карточки',
        'top_filters' => 'ТОП фильтры',
    );

    private $footers_first_item = 'top_requests';

    private $footers_settings = array(
        'top_cards' => array(
                'items_total' => 24,
                'items_per_column' => 8,
            ),
        'top_requests' => array(
                'items_total' => 24,
                'items_per_column' => 8,
            ),
        'top_filters' => array(
                'items_total' => 24,
                'items_per_column' => 8,
            ),
    );
    
    private $curr_uri;
    
    private $cache;
    
    function __construct($cache){
        $this->curr_uri = $_SERVER['REQUEST_URI'];
        $this->cache = $cache;
    }
    
    public function get_footers_cache_data(){
        foreach ($this->footers_items as $key => $value) {
            $data = $this->cache->getData('footers', $key);
            
            if($data){
                $this->footers_cache_data[$key] = $data;
            }
            
            if(empty($this->footers_cache_data[$key])){
                $this->generate_data($key);
            }elseif(count($this->footers_cache_data[$key]) < $this->footers_settings[$key]['items_total']) {
                $this->generate_data($key, $data);
            }  
        }
    }

    public function generate_data($key, $data = null){
        $method = 'generate_data_'.$key;
        if(method_exists($this, $method))
            $this->footers_cache_data[$key] = $this->$method();
        else{
            if(empty($data))
                $this->footers_cache_data[$key] = $this->generate_data_default($key);
            else
                $this->footers_cache_data[$key] = $this->generate_data_default($key, $data);
        }
    }

    public function generate_data_default($block_name, $data = null){
        $static_data = unserialize(file_get_contents(THINK_CLIENT_ROOT_PATH.'/data/footers_static_data/'.$block_name.'.php'));

        $block_data = array();
        if(!empty($data))
            $block_data = $data;

        $items_total = $this->footers_settings[$block_name]['items_total'];
        $i = 1;
        
        while (count($block_data) < $items_total) {
            if($i>50)
                break;
            $k = array_rand($static_data);
            $v = $static_data[$k];
            $block_data[$k] = $v;
            unset($static_data[$k]);
            $i++;
        }
        if(count($block_data) == $items_total && $this->cache->putData($block_data, 'footers', $block_name)){
            return $block_data;
        } else {
            return false;
        }
    }

    public function generate_data_top_menu(){
        $data = unserialize(file_get_contents(THINK_CLIENT_ROOT_PATH.'/data/footers_static_data/top_menu.php'));

        return $data;
    }

    public function print_footers(){
        $footers_html = $this->get_footers_html();
        return $footers_html;
    }

    public function get_footers_html(){
        $this->get_footers_cache_data();
        $footers_header_items_html = $this->get_footers_header_items_html();
        $footers_body_items_html = $this->get_footers_body_items_html();
        $footers_html = '';
        $footers_html .= $this->get_footers_assets();
        $footers_html .= '
            <div class="losb-block">
                <div class="losb-header">
                    <span class="losb-title"></span>
                    <ul class="losb-menu">
                        '.$footers_header_items_html.'
                    </ul>
                </div>
                <div class="losb-body">
                '.$footers_body_items_html.'
                </div>
            </div>
        ';
        return $footers_html;
    }

    public function get_footers_header_items_html(){
        $i = 1;
        $footers_header_items_html = '';
        foreach ($this->footers_items as $key => $value) {
            if(empty($this->footers_cache_data[$key]))
                continue;
            $footers_header_items_html .= '<span class="losb-menu-element '.(($i == 1)?'active':'').'" data-id="'.$key.'">'.$value.'</span>';
            $i++;
        }
        return $footers_header_items_html;
    }

    public function get_footers_body_items_html(){
        $i = 1;
        $footers_body_items_html = '';
        foreach ($this->footers_items as $key => $value) {
            if(empty($this->footers_cache_data[$key]))
                continue;
            if(method_exists($this, 'print_'.$key)){
                $method = 'print_'.$key;
                $footers_body_items_html .= $this->$method();
            }else{
                $footers_body_items_html .= $this->print_default($key);
            }
            $i++;
        }
        return $footers_body_items_html;
    }

    public function get_footers_assets(){
        return "<style type=\"text/css\">@media (max-width:600px){.losb-content.active{height:200px}.losb-body{height:300px}.losb-content-level-basic{overflow-y:scroll;height:300px}.losb-content-level-basic::-webkit-scrollbar{display:none}.active,.losb-menu-result{width:auto!important}.losb-menu{margin:0!important}.footer_head{overflow-x:scroll;width:100%;max-width:95%;white-space:nowrap}.footer_head::-webkit-scrollbar{width:0}.losb-menu{margin:0}.losb-content-level-left{width:100%!important;padding-left:0!important;vertical-align:top!important;border-bottom:1px solid #d3d3d3;border-right:none!important;overflow-y:scroll;max-height:110px}.losb-content-level-right{overflow-y:scroll;max-height:100px;width:100%!important;margin-top:10px}.losb-menu-element-sub.active{border:none}}@media (min-width:1024px){.losb-block{margin-bottom:30px}.losb-body{max-height:500px}.losb-content-level-basic{display:inline-block;overflow-y:scroll;height:200px}.losb-content-level-basic::-webkit-scrollbar{display:none}.block-footer{padding-left:15%;padding-right:15%}.active #top_menu_position{display:inline-block;position:absolute}.losb-content-level-right{width:650px;height:200px;overflow-y:scroll;padding-left:10px}.losb-content-level-right::-webkit-scrollbar{display:none}.losb-content-level-left{overflow-y:scroll;height:200px}.losb-body{margin-right:100px}.footer-link{color:#fff;font-size:11px}#top_filters{display:grid;grid-auto-flow:column}#top_cart{display:grid;grid-auto-flow:column}#top_request{display:grid;grid-auto-flow:column}}.block-footer::-webkit-scrollbar{width:0}.losb-content-level-left::-webkit-scrollbar{width:0}.losb-link{display:block;font-size:13px;font-style:normal;padding:2px 0;line-height:16px;text-decoration:none;color:#fff;transition:.3s}.losb-link:hover{color:#df3f11}.losb-menu-result{display:inline-grid;width:auto;padding-right:20px;white-space:normal}.losb-content-sub{height:0;overflow:hidden}.losb-content-sub.active{height:auto;overflow:auto}.losb-menu-element-sub{display:block;font-size:12px;font-style:normal;padding:2px 0;line-height:16px;cursor:pointer;color:#fff;transition:.3s}.losb-content-sub{font-size:12px;font-style:normal;line-height:16px;cursor:pointer;color:gray;transition:.3s}.losb-content-sub,.losb-menu-element-sub:hover{color:#ffb100}.losb-menu-element-sub.active{color:#ffb100;border-right:2px solid #ffb100}.losb-content-level-basic{padding-left:3px}.losb-content-level-left{width:250px;padding-left:3px;vertical-align:top;border-right:1px solid #d3d3d3}.losb-content-level-left,.losb-content-level-right{display:inline-grid}.losb-menu{margin:0 0 15px;padding:0;padding-top:10px;list-style-type:none}.losb-menu-element{border-radius:3px;z-index:1;position:relative;height:24px;border:1px solid #fff;color:#fff;background-color:hsl(0deg 0% 0% / 0%);display:inline-grid;margin:0;padding:0 15px;line-height:24px;font-size:12px;margin-left:0;text-decoration:none;cursor:pointer;vertical-align:middle}.losb-menu-element.active{border:1px solid #ffb100;color:#ffb100}.losb-content-sub{font-size:14px}.top-menu-losb-link{display:block;font-size:13px;font-style:normal;padding:2px 0;line-height:16px;text-decoration:none;color:#fff;transition:.3s}.top-menu-losb-link:hover{color:#df3f11}.losb-title{margin:24px 0 12px;padding:0;line-height:28px;font-weight:500;font-size:24px}.losb-content{height:0;overflow:hidden}.losb-content.active{height:auto;overflow:auto}</style>".
            '<script type="text/javascript">function ready(e){"loading"!=document.readyState?e():document.addEventListener("DOMContentLoaded",e)}ready(()=>{const e=e=>{event.preventDefault(),document.querySelectorAll(".losb-menu-element").forEach(e=>{e.classList.remove("active")}),e.currentTarget.classList.add("active");let t=e.currentTarget.dataset.id;t&&c(t)},t=e=>{e.preventDefault(),document.querySelectorAll(".losb-menu-element-sub").forEach(e=>{e.classList.remove("active")}),e.currentTarget.classList.add("active");let t=e.currentTarget.dataset.id;t&&o(t)},c=e=>{document.querySelectorAll(".losb-content").forEach(e=>e.classList.remove("active")),console.log(e),document.querySelector(`.losb-content[data-id="losb-content-${e}"]`).classList.add("active")},o=e=>{document.querySelectorAll(".losb-content-sub").forEach(e=>e.classList.remove("active")),document.querySelector(`.losb-content-sub[data-id="losb-content-sub-${e}"]`).classList.add("active")};document.querySelectorAll(".losb-menu-element").forEach(t=>{t.addEventListener("click",e)}),document.querySelectorAll(".losb-menu-element-sub").forEach(e=>{e.addEventListener("click",t)})});</script>';
    }

    public function print_default($block_name){
        $html = '';
        $items_per_column = $this->footers_settings[$block_name]['items_per_column'];
        $items_total = $this->footers_settings[$block_name]['items_total'];

        $column_count = ceil($items_total/$items_per_column);
        $column_width_percangate = round(100/$column_count);
        $style = 'style="width:'.$column_width_percangate.'%;"';
        $active = '';
        if($this->footers_first_item == $block_name)
            $active = 'active';

        $i = 1;
        $items = array();
        $columns = array();
        foreach ($this->footers_cache_data[$block_name] as $item_name => $item_url) {
            if($i > $items_per_column ){
                $i = 1;
                $columns[] = '<div class="losb-menu-result" '.$style.'>'.implode('', $items).'</div>';
                $items = array();
            }
            $items[] = '<p><a class="losb-link" href="'.$item_url.'">'.mb_ucfirst($item_name).'</a></p>';
            $i++;
        }
        $columns[] = '<div class="losb-menu-result" '.$style.'>'.implode('', $items).'</div>';
        
        $html = '<div class="losb-content '.$active.'" data-id="losb-content-'.$block_name.'">';
        $html .=     '<div class="losb-content-level-basic">'.implode('', $columns).'</div>';
        $html .= '</div>';

        return $html;
    }

    public function print_top_menu(){
        $active = '';
        if($this->footers_first_item == 'top_menu')
            $active = 'active';
        $top_menu_html = '';
        if(!empty($this->footers_cache_data['top_menu'])){
            $left_column = '';
            $right_column = '';
            $i = 1;
            $left_column_items = array();
            $right_column_items = array();
            foreach ($this->footers_cache_data['top_menu'] as $title => $value) {
                $left_column_items[] = 
                    '<div class="losb-menu-element-sub '.(($i == 1)?'active':'').'" data-id="'.$i.'">'.
                    (
                        ($value['url'])? '<a href="'.$value['url'].'">'.$title.'</a></div>': 
                        $title.'</div>'
                    );
                $items_count = count($value['childs']);
                $big_half = ceil($items_count/3);
                $ii = 1;
                $items_block_html = array();
                $items_html = array();
                foreach ($value['childs'] as $item_name => $item_url) {
                    if($ii > $big_half ){
                        $ii = 1;
                        $items_block_html[] = '<div class="losb-menu-result">'.implode('', $items_html).'</div>';
                        $items_html = array();
                    }
                    $items_html[] = '<p><a class="losb-link" href="'.$item_url.'">'.$item_name.'</a></p>';
                    $ii++;
                }
                $items_block_html[] = '<div class="losb-menu-result">'.implode('', $items_html).'</div>';
                $right_column_items[] = '<div class="losb-content-sub '.(($i == 1)?'active':'').'" data-id="losb-content-sub-'.$i.'">'.implode('', $items_block_html).'</div>';
                $i++;
            }
            $top_menu_html = '<div class="losb-content '.$active.'" data-id="losb-content-top_menu">';
            $top_menu_html .=    '<div class="losb-content-level-left">';
            $top_menu_html .=       implode('', $left_column_items);
            $top_menu_html .=    '</div>';
            $top_menu_html .=    '<div class="losb-content-level-right">';
            $top_menu_html .=       implode('', $right_column_items);
            $top_menu_html .=    '</div>';
            $top_menu_html .= '</div>';
        }
        return $top_menu_html;
    }

}
