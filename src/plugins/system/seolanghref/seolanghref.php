<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.remember
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Menus\Administrator\Helper\AssociationsHelper;
use Joomla\Module\Menu\Site\Helper\MenuHelper;

/**
 * Joomla! System Remember Me Plugin
 *
 * @since  1.5
 */

class PlgSystemSeolanghref extends CMSPlugin
{
	
    /**
     * Change Head Links
     *
     * @return  void
     * @since   1.0.0
	 */

    public function onBeforeCompileHead()
    {
	    $doc = Factory::getDocument();

	    $app  = Factory::getApplication();

		if($app->isClient('site') && !empty($associations))
		{
		    $active = $app->getMenu()->getActive();
		    $associations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $active->id);
		
		    $lang        = Factory::getLanguage();
		    $currentLanguageTag = $lang->get('tag');
		    $currentLanguageTagShort = substr(strtolower($lang->get('tag')), 0, 2);
			
		    $head_data = $doc->getHeadData();

		    $links     = $head_data['links'];
			
			$allLanguages  = LanguageHelper::getLanguages('lang_code');

			$baseUrl = Uri::base();

			$currentUrl = Uri::current();

	        foreach ($allLanguages as $langTag => $values)
	        {
				$langTagShort = substr($langTag, 0, 2);

				if($langTag !== $currentLanguageTag)
				{
					$menuItemId = (int) $associations[$langTag]->id;
			        $menu = $app->getMenu();
					$menuItem = $menu->getItem( $menuItemId );

					$link = Route::_($menuItem->link . '&Itemid=' . $menuItem->id . '&lang=' . $langTagShort);

					unset($doc->_links[$baseUrl . substr($link, 1)]);
					$doc->addHeadLink($baseUrl . substr($link, 1), 'alternate', 'rel',['hreflang' => $langTagShort]);
				}
				else{
					$link = Route::_($active->link . '&Itemid=' . $active->id . '&lang=' . $currentLanguageTagShort);
					unset($doc->_links[$baseUrl . substr($link, 1)]);
					$doc->addHeadLink($baseUrl . substr($link, 1), 'canonical', 'rel',['hreflang' => $currentLanguageTagShort]);
					$doc->addHeadLink($baseUrl . substr($link, 1) . '####', 'alternate', 'rel',['hreflang' => $currentLanguageTagShort]);
				}
	        }
		}
		
	    if($app->isClient('site') && Factory::getLanguage()->get('tag') == 'de-DE')
	    {
		    $lang        = Factory::getLanguage();
		    $currentLanguageTagShort = substr(strtolower($lang->get('tag')), 0, 2);
		    $currentUrl = Uri::current();
			
		    $doc->addHeadLink($currentUrl, 'canonical', 'rel',['hreflang' => $currentLanguageTagShort]);
		    $doc->addHeadLink($currentUrl . '####', 'alternate', 'rel',['hreflang' => $currentLanguageTagShort]);
	    }
	    else{
		    $currentUrl = Uri::current();
		    $doc->addHeadLink($currentUrl, 'canonical', 'rel');
		    $doc->addHeadLink($currentUrl . '####', 'alternate', 'rel');
	    }
    }

	/**
	 * Listener for the `onAfterRender` event
	 *
	 * @return  void
	 * @throws  \Exception
	 *
	 * @since   1.0.0
	 */
	public function onAfterRender()
	{

		$app = Factory::getApplication();
		if ($app->isClient('administrator'))
		{
			return;
		}

		$body = $app->getBody();
		$bodyNew = str_replace('####', '', $body);
		$app->setBody($bodyNew);
	}

}
