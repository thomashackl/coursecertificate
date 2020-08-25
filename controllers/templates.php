<?php

/**
 * Class TemplatesController
 * Controller for defining and editing certificate templates
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    CourseCertificate
 */

class TemplatesController extends AuthenticatedController {

    /**
     * Actions and settings taking place before every page call.
     */
    public function before_filter(&$action, &$args)
    {
        $this->plugin = $this->dispatcher->plugin;
        $this->flash = Trails_Flash::instance();

        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }

        $this->set_layout(Request::isXhr() ? null : $GLOBALS['template_factory']->open('layouts/base'));

        Navigation::activateItem('/tools/coursecertificate/templates');
    }

    public function index_action()
    {
        PageLayout::setTitle(dgettext('coursecertificate', 'Vorlagen für Teilnahmezertifikate'));
        $this->templates = CourseCertificateTemplate::findBySQL("1 ORDER BY `name`");

        $sidebar = Sidebar::get();
        $actions = new ActionsWidget();
        $actions->addLink(dgettext('coursecertificate', 'Vorlage hinzufügen'),
            $this->link_for('templates/edit'),
            Icon::create('add'))->asDialog('size=auto');
        $sidebar->addWidget($actions);
    }

    public function edit_action($id = 0)
    {
        PageLayout::setTitle($id == 0 ?
            dgettext('coursecertificate', 'Vorlage hinzufügen') :
            dgettext('coursecertificate', 'Vorlage bearbeiten'));

        $this->template = ($id == 0 ? new CourseCertificateTemplate() : CourseCertificateTemplate::find($id));
    }

}
