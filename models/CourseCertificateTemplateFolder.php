<?php
/**
 * CourseCertificateTemplateFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    CourseCertificate
 */
class CourseCertificateTemplateFolder extends PublicFolder
{

    public static $sorter = 1;

    /**
     * Returns a localised name of the CourseCertificateTemplateFolder type.
     *
     * @return string The localised name of this folder type.
     */
    static public function getTypeName()
    {
        return dgettext('coursecertificate', 'Enthält alle Vorlagen für Teilnahmezertifikate.');
    }

    /**
     * @param Object|string $range_id_or_object
     * @param string $user_id
     * @return bool
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }


    /**
     * @param string $role
     * @return Icon
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = $this->is_empty
               ? 'folder-public-empty'
               : 'folder-public-full';

        return Icon::create($shape, $role);
    }


    /**
     * CourseCertificateTemplateFolders are invisible, only indirectly accessible
     * via the CourseCertificateTemplate class.
     *
     * @param string $user_id The user who wishes to see the folder.
     *
     * @return bool False
     */
    public function isVisible($user_id)
    {
        return false;
    }

    /**
     * CourseCertificateTemplateFolders are not readable, only indirectly accessible
     * via the CourseCertificateTemplate class.
     *
     * @param string $user_id The user who wishes to read the folder.
     *
     * @return bool False
     */
    public function isReadable($user_id)
    {
        return $this->isVisible($user_id);
    }

    /**
     * CourseCertificateTemplateFolders are writable for everyone,
     * but are only accessed indirectly via the CourseCertificateTemplate class
     *
     * @param string $user_id The user who wishes to read the folder.
     *
     * @return bool False
     */
    public function isWritable($user_id)
    {
        return true;
    }

    /**
     * Returns a description template for CourseCertificateTemplateFolders.
     *
     * @return string A string describing this folder type.
     */
    public function getDescriptionTemplate()
    {
        return dgettext('coursecertificate', 'Hier liegen die Vorlagen für Teilnahmezertifikate.');

    }

    /**
     * Files in CourseCertificateTemplateFolders are always downloadable.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to downlaod the file.
     *
     * @return bool True
     */
    public function isFileDownloadable($file_id, $user_id)
    {
        return true;
    }

    /**
     * Files in CourseCertificateTemplateFolders are not editable.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to edit the file.
     *
     * @return bool False
     */
    public function isFileEditable($file_id, $user_id)
    {
        return false;
    }

    /**
     * Files in CourseCertificateTemplateFolders are not writable.
     *
     * @param string $file_id The ID to a FileRef.
     * @param string $user_id The user who wishes to write to the file.
     *
     * @return bool False
     */
    public function isFileWritable($file_id, $user_id)
    {
        return false;
    }

    /**
     * Returns the edit template for this folder type.
     *
     * @return Flexi_Template
     */
    public function getEditTemplate()
    {
        return null;
    }

    /**
     * Sets the data from a submitted edit template.
     *
     * @param array $request The data from the edit template.
     *
     * @return PublicFolder A "reference" to this CourseCertificateTemplateFolder.
     */
    public function setDataFromEditTemplate($request)
    {
        return $this;
    }

    /**
     * Gets or creates a folder for course certificate templates.
     *
     * @return SimpleORMap
     */
    public static function get()
    {
        if ($folder = Folder::findOneBySQL("`folder_type` = 'CourseCertificateTemplateFolder'")) {

            return $folder;

        } else {

            $folder = new self();
            $folder->user_id = User::findCurrent()->id;
            $folder->parent_id = '';
            $folder->range_id = 'studip';
            $folder->range_type = '';
            $folder->folder_type = 'CourseCertificateTemplateFolder';
            $folder->name = 'Vorlagen für Teilnahmezertifikate';
            $folder->data_content = '';
            $folder->description = '';
            $folder->store();

            return $folder;

        }
    }
}
