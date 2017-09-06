<?php

/**
 * Class OpenGraphMeta
 *
 * @property string                 OGTitle
 * @property string                 OGContent
 * @property string                 OGDescription
 *
 * @method Image OGImage()
 *
 * @property SiteTree|OpenGraphMeta $owner
 */
class OpenGraphMeta extends DataExtension
{
    private static $db = [
        'OGTitle'       => 'Varchar(255)',
        'OGContent'     => 'Enum("website,article,blog,product,profile,video,place","website")',
        'OGDescription' => 'Text'
    ];

    private static $has_one = [
        'OGImage' => 'Image'
    ];

    public static $defaults = [
        'OGContent' => 'website'
    ];

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', ToggleCompositeField::create('OpenGraph', 'Open Graph',
            [
                LiteralField::create('', '<h2>&nbsp;&nbsp;&nbsp;Open Graph Information <img style="position:relative;top:8px;" src="' .
                                         Director::absoluteBaseURL() . 'open-graph-meta/images/opengraph.png"></h2>'),
                TextField::create('OGTitle', '')
                    ->setAttribute('placeholder', 'e.g My Website')
                    ->setRightTitle('Page title goes here, automatically defaults to the page title'),
                DropdownField::create('OGContent', 'Content Type', $this->owner->dbObject('OGContent')->enumValues())
                    ->setRightTitle('Will default to website (the most common open graph object type'),
                TextAreaField::create('OGDescription', '')
                    ->setRightTitle('Page description goes here, automatically defaults to the content summary'),
                UploadField::create('OGImage', 'Open Graph Image')
                    ->setDescription('Ideal size: 1200px * 630px')
            ]
        ));
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
         * @var OpenGraphSiteConfigExtension $siteConfig
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

    /**
     * @param string $tags
     */
    public function MetaTags(&$tags)
    {
        // Title
        if (trim($this->owner->OGTitle)) {
            $tags .= sprintf('<meta property="og:title" content="%s">', $this->owner->OGTitle) . "\n";
        } else {
            $tags .= sprintf('<meta property="og:title" content="%s">', $this->owner->Title) . "\n";
        }

        // URL
        $tags .= sprintf('<meta property="og:url" content="%s">', $this->owner->AbsoluteLink()) . "\n";

        // Type
        if ($this->owner->OGContent) {
            $tags .= sprintf('<meta property="og:type" content="%s">', $this->owner->OGContent) . "\n";
        } else {
            $tags .= '<meta property="og:type" content="website">' . "\n";
        }

        // Description
        if (trim($this->owner->OGDescription)) {
            $tags .= sprintf('<meta property="og:description" content="%s">', $this->owner->OGDescription) . "\n";
        } elseif ($this->owner->Content) {
            $tags .= sprintf('<meta property="og:description" content="%s">', $this->owner->dbObject('Content')->FirstParagraph()) . "\n";
        }

        // Image
        $image = $this->owner->getOGImageURL();

        $this->owner->extend('updateOpenGraphImage', $image);

        if (!empty($image)) {
            $tags .= sprintf('<meta property="og:image" content="%s">', $image) . "\n";
        }
    }
}
