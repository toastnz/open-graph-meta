<?php

/**
 * Class OpenGraphMeta
 *
 * @property SiteTree $owner
 */
class OpenGraphMeta extends DataExtension
{
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
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', ToggleCompositeField::create('OpenGraph', 'Open Graph',
            array(
                LiteralField::create('', '<h2>&nbsp;&nbsp;&nbsp;Open Graph Information <img style="position:relative;top:8px;" src="' . Director::absoluteBaseURL() . 'open-graph-meta/images/opengraph.png"></h2>'),
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


    public function onBeforeWrite()
    {
        /** =========================================
         * @var SiteConfig $siteConfig
        ===========================================*/

        parent::onBeforeWrite();

        $siteConfig = SiteConfig::current_site_config();

        if ($this->owner->exists()) {
            if ($this->owner->isChanged('Content') && !$this->owner->OGDescription) {
                $this->owner->setField('OGDescription', $this->owner->dbObject('Content')->FirstParagraph());
            }
            if ($this->owner->isChanged('Title') && !$this->owner->OGTitle) {
                $this->owner->setField('OGTitle', $this->owner->Title);
            }
            if (!$this->owner->OGImageID) {
                $this->owner->setField('OGImageID', $siteConfig->DefaultOpenGraphImageID);
            }
        }

        if (!$this->owner->OGContent) {
            $this->owner->OGContent = 'website';
        }

        if (!$this->owner->OGDescription) {
            $this->owner->setField('OGDescription', $this->owner->dbObject('Content')->FirstParagraph());
        }
    }

    public function FirstImage()
    {
        $pattern = ' /<img[^>]+ src[\\s = \'"]';
        $pattern .= '+([^"\'>\\s]+)/is';
        if (preg_match_all($pattern, $this->owner->Content, $match)) {
            $imageLink = preg_replace('/_resampled\/resizedimage[0-9]*-/', '', $match[1][0]);
            return (string)$imageLink;
        } else {
            return '';
        }
    }

    public function getFirstImage()
    {
        return $this->owner->FirstImage();
    }

    /**
     * Controller logic for returning Open Graph image
     *
     * @return String
     */
    public function getOGImageURL()
    {
        /** =========================================
         * @var SiteConfig $siteConfig
         * ========================================*/

        $siteConfig = SiteConfig::current_site_config();

        if ($this->owner->OGImage() && $this->owner->OGImage()->exists()) {
            return $this->owner->OGImage()->Fit(1200, 630)->AbsoluteURL;
        } elseif ($firstImage = $this->owner->getFirstImage()) {
            return Controller::join_links(Director::absoluteBaseURL(), $firstImage);
        } elseif ($siteConfig->DefaultOpenGraphImage() && $siteConfig->DefaultOpenGraphImage()->exists()) {
            return $siteConfig->DefaultOpenGraphImage()->Fit(1200, 630)->AbsoluteURL;
        }

        return '';
    }

    public function getOGUrlForTemplate()
    {
        if ($this->owner->OGUrl) {
            if (strpos($this->owner->OGUrl, 'http://') === 0 || strpos($this->owner->OGUrl, 'https://') === 0) {
                // URL is absolute, return
                return $this->owner->OGUrl;
            }
            return Controller::join_links(Director::absoluteBaseURL(), $this->owner->OGUrl);
        } else {
            return $this->owner->AbsoluteLink();
        }
    }

    /**
     * @param string $tags
     */
    public function MetaTags(&$tags)
    {
        // Title
        if ($this->owner->OGTitle) {
            $tags .= sprintf('<meta name="og:title" content="%s">', $this->owner->OGTitle) . "\n";
        } else {
            $tags .= sprintf('<meta name="og:title" content="%s">', $this->owner->Title) . "\n";
        }

        // URL
        $tags .= sprintf('<meta name="og:url" content="%s">', $this->owner->getOGUrlForTemplate()) . "\n";

        // Type
        if ($this->owner->OGContent) {
            $tags .= sprintf('<meta name="og:type" content="%s">', $this->owner->OGContent) . "\n";
        } else {
            $tags .= '<meta name="og:type" content="website">' . "\n";
        }

        // Description
        if ($this->owner->OGDescription) {
            $tags .= sprintf('<meta name="og:description" content="%s">', $this->owner->OGDescription) . "\n";
        } elseif ($this->owner->Content) {
            $tags .= sprintf('<meta name="og:description" content="%s">', $this->owner->dbObject('Content')->FirstParagraph()) . "\n";
        }

        // Image
        $image = $this->owner->getOGImageURL();

        $this->owner->extend('updateOpenGraphImage', $image);

        $tags .= sprintf('<meta name="og:image" content="%s">', $image) . "\n";
    }
}
