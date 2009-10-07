<?php
/**
 * Nested Urls SilverStripe Module
 * 
 * @author James Muir <james.p.muir@gmail.com>
 * @copyright Copyright (c) 2009, James Muir
 */

/**
 * NestedUrlPageExtension
 */
class NestedUrlPageExtension extends DataObjectDecorator
{
    /**
     * Add UrlParent has_one to SiteTree
     *
     * @return array
     */
	public function extraStatics() {
		return array(
			'has_one' => array(
				'UrlParent' => 'SiteTree',
			),
		);
	}

    /**
     * Add UrlParent SiteTree Dropdown to Behaviour Tab
     *
     * @param FieldSet $fields
     */
    public function updateCMSFields(FieldSet &$fields){
        //$baseURLField = $fields->fieldByName('BaseUrlLabel');
         $link = $this->Link();
         $link = substr($link, 1);
         $link = str_replace($this->owner->URLSegment.'/', '', $link);
        $baseUrlField = new LabelField('BaseUrlLabel',$link);
        
        $fields->addFieldToTab('Root.Content.Metadata', $baseUrlField, 'URLSegment');
        
        
        $fields->addFieldToTab('Root.Behaviour',new TreeDropdownField("UrlParentID", 'URL Parent', 'SiteTree'));
    }

   public function Link($action = null){
        $url = $this->owner->URLSegment;
        $parent = $this->owner->getComponent('UrlParent');

        // if no UrlParent, default to Parent
        if(!$parent->ID){
            $parent = $this->owner->getComponent('Parent');
        }

        while($parent->ID != 0){
            if($parent->URLSegment != 'home' ) {
                $url= $parent->URLSegment. '/'. $url;
            }

            $nextParent = $parent->getComponent('UrlParent');

           // if no UrlParent, default to Parent
            if(!$nextParent->ID){
                $nextParent = $parent->getComponent('Parent');
            }
            $parent = $nextParent;
        }
        return Director::baseURL() . $url . "/$action";
    }

}

class NestedUrlControllerExtension extends Extension {
    /**
     * Create link based on parents (UrlParents and Parents)
     *
     * @param string $action
     * @return string Link
     */
    public function Link($action = null){
        $url = $this->owner->URLSegment;
        $parent = $this->owner->getComponent('UrlParent');

        // if no UrlParent, default to Parent
        if(!$parent->ID){
            $parent = $this->owner->getComponent('Parent');
        }

        while($parent->ID != 0){
            if($parent->URLSegment != 'home' ) {
                $url= $parent->URLSegment. '/'. $url;
            }

            $nextParent = $parent->getComponent('UrlParent');

           // if no UrlParent, default to Parent
            if(!$nextParent->ID){
                $nextParent = $parent->getComponent('Parent');
            }
            $parent = $nextParent;
        }
        return Director::baseURL() . $url . "/$action";
    }
}