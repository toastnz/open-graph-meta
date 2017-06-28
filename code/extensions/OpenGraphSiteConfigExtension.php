<?php

/**
 * Class OpenGraphSiteConfigExtension
 *
 * @property SiteConfig $owner
 *
 * @method Image DefaultOpenGraphImage()
 */
class OpenGraphSiteConfigExtension extends DataExtension
{
    private static $has_one = [
        'DefaultOpenGraphImage' => 'Image'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {

        if (!$fields->fieldByName('Root.Settings')) {
            $fields->addFieldToTab('Root', TabSet::create('Settings'));
        }

        /** -----------------------------------------
         * Details
         * ----------------------------------------*/

        $fields->findOrMakeTab('Root.Settings.OpenGraph');
        $fields->addFieldsToTab('Root.Settings.OpenGraph', [
            HeaderField::create('', 'Open Graph'),
            UploadField::create('DefaultOpenGraphImage', 'Default Facebook Share Image')
        ]);
    }
}
