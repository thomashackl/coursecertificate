<?php

require_once 'app/controllers/studip_controller.php';

class IndexController extends StudipController {

    /**
     * Diese Methode wird bei jedem Pfad aufgerufen
     */
    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);

        // Lade das standard StudIP Layout
        $this->set_layout($GLOBALS['template_factory']->open('layouts/base'));

        $quicksearch = QuickSearch::get("username", new StandardSearch("username"))
                ->withButton()
                ->setInputStyle("width: 240px");

        $quicksearch->defaultValue(Request::get('username'), Request::get('username_parameter'));
        $this->quicksearch = $quicksearch->render();
        $this->templates = $this->load_templates();
    }

    public function index_action() {
        $this->semester = array();
        if ($user = Request::get('username')) {
            $classname = "certificate_" . Request::get('certificate');
            $this->cert = new $classname($user, Request::getArray('whitelist'));
            $this->tree = $this->cert->tree;
            $this->semester = $this->cert->semester;
        }
        if (Request::submitted('create')) {
            $this->cert->export($this->allCourses);
        }
    }

    private function load_templates() {
        $paths = glob($this->dispatcher->trails_root . '/templates/*.php');
        $templates = array();
        foreach ($paths as $path) {
            include $path;
            $classname = "certificate_" . basename($path, ".php");
            $class = new $classname;
            $templates[basename($path, ".php")]['name'] = $class->name;
            if (Request::get('certificate') == basename($path, ".php")) {
                $templates[basename($path, ".php")]['selected'] = "selected='selected'";
            }
            $this->certificate[basename($path, ".php")] = $class;
        }
        return $templates;
    }

}

?>
