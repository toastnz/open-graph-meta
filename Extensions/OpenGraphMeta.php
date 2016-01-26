<?php

/**
 * Pinc
 */
class OpenGraphMeta extends DataExtension {


    /**
     * @var array
     */
    private static $db = array(
        'OGTitle' => 'Varchar(255)',
        'OGContent' => 'Varchar(512)',
        'OGDescription' => 'Text',
        'OGUrl' => 'Varchar(512)'
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'OGImage' => 'Image'
    );

    /**
     * @param FieldList $fields
     * @return Object
     */
    public function updateCMSFields(FieldList $fields) {

        $fields->addFieldsToTab('Root.Main', ToggleCompositeField::create('Open graph', 'Open graph',
            array(
                LiteralField::create('', '<h2>&nbsp;&nbsp;&nbsp;Open Graph Information <img style="position:relative;top:8px;" src="' . Director::absoluteBaseURL() . 'open-graph-meta/Images/opengraph.png"></h2>'),
                TextField::create('OGTitle', '')->setAttribute('placeholder', 'e.g My Website')->setRightTitle('Page title goes here, automatically defaults to the page title'),
                TextField::create('OGUrl', '')->setAttribute('placeholder', 'http://www.mywebsite.com/')->setRightTitle('Page URL goes here, automatically defaults to the page URL (shouldn\'t need overwriting)'),
                DropdownField::create('OGContent', 'Content Type', array(
                    'website' => 'website',
                    'article' => 'article',
                    'blog' => 'blog',
                    'product' => 'product',
                    'profile' => 'profile',
                    'video' => 'video',
                    'place' => 'place',
                ), 'website')->setRightTitle('Will default to website (the most common open graph object type'),
                TextAreaField::create('OGDescription', '')->setRightTitle('Page description goes here, automatically defaults to the content summary'),
                UploadField::create('OGImage', 'Open Graph Image')
            )
        ));
    }

    public function onBeforeWrite() {
        parent::onBeforeWrite();
        if ($this->owner->ID) {
            if ($this->owner->OGTitle == '') {
                $this->owner->OGTitle = $this->owner->Title;
            }
            if ($this->owner->OGUrl == '') {
                $this->owner->OGUrl = $this->owner->AbsoluteLink();
            }
        }

        if ($this->owner->OGContent == '') {
            $this->owner->OGContent = 'website';
        }
        if ($this->owner->OGDescription == '') {
            $this->owner->OGDescription = $this->owner->dbObject('Content')->Summary(50);
        }
        if (!$this->owner->OGImageID) {
            $this->owner->OGImageID = SiteConfig::current_site_config()->DefaultOpenGraphImage()->ID;
        }
    }

    public function FirstImage() {
        $pattern = ' /<img[^>]+ src[\\s = \'"]';
        $pattern .= '+([^"\'>\\s]+)/is';
        if (preg_match_all($pattern, $this->owner->Content, $match)) {
            $imageLink = preg_replace('/_resampled\/resizedimage[0-9]*-/', '', $match[1][0]);
            return (string) $imageLink;
        } else {
            return;
        }
    }


}
