<?php

require_once "vendor/fpdf/fpdf.php";
require_once "vendor/fpdi/fpdi.php";

class CourseCertificatePlugin extends StudipPlugin implements SystemPlugin {

    function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__.'/trails/models');

        // Lade den Navigationsabschnitt "tools"
        $navigation = Navigation::getItem('/tools');

        // Erstelle einen neuen Navigationspunkt
        $cert_navi = new Navigation(_('Teilnahmezertifikat'), PluginEngine::getUrl('CourseCertificatePlugin/index'));

        // Binde disen Punkt unter "tools" ein
        $navigation->addSubNavigation('coursecert', $cert_navi);
    }

    /**
     * Wird das Plugin tatsächlich aufgerufen, so landen wir in der perform
     * Methode
     * 
     * @param string Die restliche Pfadangabe
     */
    function perform($unconsumed_path) {

        /*
         * Jetzt brauchen wir nur noch einen Trailsdispatcher der die restliche
         * Arbeit für uns erledigt. An dieser Stelle springt also die Plugin-
         * verarbeitung weiter in den Trailsordner
         */
        $trails_root = $this->getPluginPath() . "/trails";
        $dispatcher = new Trails_Dispatcher($trails_root, PluginEngine::getUrl('coursecertificateplugin/index'), 'index');
        $dispatcher->dispatch($unconsumed_path);
    }

}
