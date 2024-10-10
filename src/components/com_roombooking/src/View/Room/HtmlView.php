<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\View\Room;

defined('_JEXEC') or die;

use NumberFormatter;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Roombooking\Site\Helper\RoombookingHelper;

/**
 * HTML Room View class for the Roombooking component
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The item model state
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  1.6
	 */
	protected $state;

	/**
	 * The item object details
	 *
	 * @var    \JObject
	 * @since  1.0.0
	 */
	protected $item;

	/**
	 * The component params
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  1.6
	 */
	protected $params;


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null): void
	{
		$app = Factory::getApplication();
		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->params = $app->getParams('com_roombooking');

		// Check for errors.
		if (\count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Create a shortcut for $item.
		$item = $this->item;

		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
		$item->price = RoombookingHelper::formatPrice($item->price);

		parent::display($tpl);
	}
}
