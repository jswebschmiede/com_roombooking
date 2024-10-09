<?php

namespace Joomla\Component\RoomBooking\Administrator\View\Bookings;

defined('_JEXEC') or die;

use JUser;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View class for a list of bookings.
 *
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Joomla user object
	 *
	 * @var JUser
	 */
	protected $user;

	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  1.6
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.6
	 */
	public $activeFilters = [];

	/**
	 * An array of items
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Get the state
	 *
	 * @return Registry
	 */
	public function getState(): Registry
	{
		return $this->state;
	}

	/**
	 * Get the items
	 *
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * Get the pagination
	 *
	 * @return Pagination
	 */
	public function getPagination(): Pagination
	{
		return $this->pagination;
	}

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null): void
	{
		$this->user = Factory::getApplication()->getIdentity();

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors
		if (count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

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

		ToolbarHelper::title(Text::_('COM_ROOMBOOKING_MANAGER_BOOKINGS'), 'bookmark bookings');

		if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_roombooking', 'core.create')) > 0) {
			$toolbar->addNew('booking.add');
		}

		if ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))) {
			/** @var  DropdownButton $dropdown */
			$dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state')) {
				if ($this->state->get('filter.published') != 2) {
					$childBar->publish('bookings.publish')->listCheck(true);

					$childBar->unpublish('bookings.unpublish')->listCheck(true);
				}

				if ($this->state->get('filter.published') != -1) {
					if ($this->state->get('filter.published') != 2) {
						$childBar->archive('bookings.archive')->listCheck(true);
					} elseif ($this->state->get('filter.published') == 2) {
						$childBar->publish('bookings.publish')->task('bookings.publish')->listCheck(true);
					}
				}

				$childBar->checkin('bookings.checkin');

				if ($this->state->get('filter.published') != -2) {
					$childBar->trash('bookings.trash')->listCheck(true);
				}
			}

			if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
				$toolbar->delete('bookings.delete', 'JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		if ($user->authorise('core.admin', 'com_roombooking') || $user->authorise('core.options', 'com_roombooking')) {
			$toolbar->preferences('com_roombooking');
		}
	}
}
