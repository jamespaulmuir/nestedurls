<?php
/**
 * Nested Urls SilverStripe Module
 * Config file
 * @author James Muir <james.p.muir@gmail.com>
 * @copyright Copyright (c) 2009, James Muir
 */

/**
 * Add Extension to SiteTree class
 */
Object::add_extension('SiteTree', 'NestedUrlPageExtension');
Object::add_extension('ContentController', 'NestedUrlControllerExtension');

/**
 * Add Rules to director (once page is found, will fall back to ModelAsController)
 */
Director::addRules(2, array(
	'$url1/$url2/$url3/$url4/$url5/$url6/$url7/$url8/$url9' => 'NestedUrlController',
));

?>
