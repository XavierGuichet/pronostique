<?php

class TemplateEngine {

    private $base_path;

    public function __construct($path)
    {
        $this->base_path = $path;
    }

    public function display($template, $params)
    {
        $template_path = $this->base_path.'/partials/'.$template.'.php';
        if (!file_exists($template_path)) {
            trigger_error('Unknow template call : '.$template, E_USER_NOTICE);
            return '';
        }

        extract($params);
        ob_start();
        include $template_path;
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}
