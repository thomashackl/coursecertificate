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

        $psearch = new PermissionSearch('user',
            _('Person suchen'),
            'username',
            array(
                'permission' => array('user', 'autor', 'tutor', 'dozent'),
                'exclude_user' => array()
            )
        );

        $quicksearch = QuickSearch::get("username", $psearch)
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
            $templates[basename($path, ".php")]['order'] = $class->order ?: 99;
            $templates[basename($path, ".php")]['path'] = basename($path, ".php");
            if (Request::get('certificate') == basename($path, ".php")) {
                $templates[basename($path, ".php")]['selected'] = "selected='selected'";
            }
            $this->certificate[basename($path, ".php")] = $class;
        }
        usort($templates, function($a, $b) {
            return $a['order'] > $b['order'];
        });
        return $templates;
    }

}
