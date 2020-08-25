<?php

/**
 * Adds a database table for storing course certificate templates.
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

class CertificateTemplates extends Migration {

    public function description()
    {
        return 'Adds a database table for storing course certificate templates.';
    }

    public function up()
    {

        DBManager::get()->execute("CREATE TABLE IF NOT EXISTS `coursecertificate_templates`
        (
            `template_id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `fileref_id` VARCHAR(32) NOT NULL COLLATE latin1_bin,
            `user_id` VARCHAR(32) NOT NULL COLLATE latin1_bin,
            `mkdate` DATETIME NOT NULL,
            `chdate` DATETIME NOT NULL,
            PRIMARY KEY (`template_id`)
        ) ENGINE InnoDB ROW_FORMAT=DYNAMIC");
    }

    /**
     * Migration DOWN: cleanup all created data.
     */
    public function down()
    {
        DBManager::get()->execute("DROP TABLE IF EXISTS `coursecertificate_templates`");
    }

}
