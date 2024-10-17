<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\View\Rooms;

use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Roombooking\Site\Helper\RouteHelper;
use Joomla\Component\Roombooking\Site\Helper\RoombookingHelper;

defined('_JEXEC') or die;

/**
 * Rooms list view
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $items = [];

	/**
	 * The component params
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  1.6
	 */
	protected $params;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The state object
	 *
	 * @var    \Joomla\CMS\Object\CMSObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * The filter form
	 *
	 * @var    \Joomla\CMS\Form\Form
	 * @since  1.6
	 */
	protected $filterForm;

	/**
	 * The active filters
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $activeFilters;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null): void
	{
		$this->state = $this->get('State');
		$this->params = $this->state->get('params');
		$this->items = $this->get('Items');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->pagination = $this->get('Pagination');

		// Flag indicates to not add limitstart=0 to URL
		$this->pagination->hideEmptyLimitstart = true;

		foreach ($this->items as &$item) {
			$item->link = Route::_(RouteHelper::getRoomRoute($item->id, $item->alias));
			$item->price = RoombookingHelper::formatPrice($item->price);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		parent::display($tpl);
	}
}
