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
        if (!$fields->fieldByName('Root.Metadata')) {
            $fields->addFieldToTab('Root', TabSet::create('Metadata'));
        }

        /** -----------------------------------------
         * Details
         * ----------------------------------------*/

        $fields->findOrMakeTab('Root.Metadata.OpenGraph', 'Facebook');

        $fields->addFieldsToTab('Root.Metadata.OpenGraph', [
            HeaderField::create('', 'Open Graph'),
            UploadField::create('DefaultOpenGraphImage', 'Default Facebook Share Image')
        ]);
    }
}
