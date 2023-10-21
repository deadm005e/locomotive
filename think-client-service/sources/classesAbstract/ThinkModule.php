<?php 
abstract class ThinkModule extends ThinkClientHtmlMethods
{
    abstract protected function replace_html();
    abstract protected function generate();
    abstract protected function validate();
}