<?php

/**
 * CourseCertificateTemplate.php
 * model class for course certificate templates.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    CourseCertificate
 *
 * @property int template_id database column
 * @property string name database column
 * @property string template_file database column
 * @property string creator database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class CourseCertificateTemplate extends SimpleORMap
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'coursecertificate_templates';
        $config['belongs_to']['fileref'] = [
            'class_name' => 'FileRef',
            'foreign_key' => 'fileref_id',
            'assoc_foreign_key' => 'id'
        ];
        $config['has_one']['creator'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
            'assoc_foreign_key' => 'user_id'
        ];
        $config['additional_fields']['type'] = true;
        $config['additional_fields']['filename'] = true;

        parent::configure($config);
    }

    public function getType()
    {
        return pathinfo($this->fileref->file->name);
    }

    public function getFilename()
    {
        return $this->fileref->file->name;
    }

}
