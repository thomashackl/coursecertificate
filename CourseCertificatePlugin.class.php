<?php

class CourseCertificatePlugin extends StudipPlugin implements SystemPlugin {

    function __construct() {
        parent::__construct();

        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

        // Localization
        bindtextdomain('coursecertificate', realpath(__DIR__.'/locale'));

        if ($GLOBALS['perm']->have_perm('admin')) {
            $navigation = new Navigation($this->getDisplayName(),
                PluginEngine::getURL($this, [], 'certificate'));

            $navigation->addSubNavigation('certificates',
                new Navigation(dgettext('coursecertificate', 'Zertifikat erstellen'),
                    PluginEngine::getURL($this, [], 'certificate')));

            if ($GLOBALS['perm']->have_perm('root')) {
                $navigation->addSubNavigation('templates',
                    new Navigation(dgettext('coursecertificate', 'Vorlagen verwalten'),
                        PluginEngine::getURL($this, [], 'templates')));
            }

            Navigation::addItem('/tools/coursecertificate', $navigation);
        }

    }

    /**
     * Plugin name to show in navigation.
     */
    public function getDisplayName()
    {
        return dgettext('coursecertificate', 'Teilnahmezertifikat');
    }

    function perform($unconsumed_path) {
        $range_id = Request::option('cid', Context::get()->id);

        URLHelper::removeLinkParam('cid');
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, [], null), '/'),
            'media'
        );
        URLHelper::addLinkParam('cid', $range_id);

        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

}
