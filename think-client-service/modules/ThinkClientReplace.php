<?php
/**
 * Модуль для подмены html
 */
class ThinkClientReplace extends ThinkClientHtmlMethods
{
    function __construct($html) {
       parent::__construct($html);
    }

    public function replace_html()
    {
        // add comment
        $this->html = str_replace("</head>", "<!-- comment -->\n</head>", $this->html);

        return $this->html;
    }
}
