<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\View\Rooms;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View class for a list of rooms.
 * 
 * @property-read array $items
 * @property-read Pagination $pagination
 * @property-read Registry $state
 * 
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	public $activeFilters = [];

	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Is this view an Empty State
	 *
	 * @var  boolean
	 * @since 1.0.0
	 */
	private $isEmptyState = false;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 * @throws  \Exception
	 */
	public function display($tpl = null): void
	{
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled()) {
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar(): void
	{
		$canDo = ContentHelper::getActions('com_roombooking');
		$user = $this->getCurrentUser();
		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(Text::_('COM_ROOMBOOKING_MANAGER_ROOMS'), 'bookmark rooms');

		if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_roombooking', 'core.create')) > 0) {
			$toolbar->addNew('room.add');
		}

		if (!$this->isEmptyState && ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')))) {
			/** @var  DropdownButton $dropdown */
			$dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state')) {
				if ($this->state->get('filter.published') != 2) {
					$childBar->publish('rooms.publish')->listCheck(true);

					$childBar->unpublish('rooms.unpublish')->listCheck(true);
				}

				if ($this->state->get('filter.published') != -1) {
					if ($this->state->get('filter.published') != 2) {
						$childBar->archive('rooms.archive')->listCheck(true);
					} elseif ($this->state->get('filter.published') == 2) {
						$childBar->publish('publish')->task('rooms.publish')->listCheck(true);
					}
				}

				$childBar->checkin('rooms.checkin');

				if ($this->state->get('filter.published') != -2) {
					$childBar->trash('rooms.trash')->listCheck(true);
				}
			}

			if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
				$toolbar->delete('rooms.delete', 'JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}

			// Add a batch button
			if (
				$user->authorise('core.create', 'com_roombooking')
				&& $user->authorise('core.edit', 'com_roombooking')
				&& $user->authorise('core.edit.state', 'com_roombooking')
			) {
				$childBar->popupButton('batch', 'JTOOLBAR_BATCH')
					->popupType('inline')
					->textHeader(Text::_('COM_ROOMBOOKING_BATCH_OPTIONS'))
					->url('#joomla-dialog-batch')
					->modalWidth('800px')
					->modalHeight('fit-content')
					->listCheck(true);
			}
		}

		if ($user->authorise('core.admin', 'com_roombooking') || $user->authorise('core.options', 'com_roombooking')) {
			$toolbar->preferences('com_roombooking');
		}
	}
}
