<?php

namespace Joomla\Component\RoomBooking\Administrator\View\Booking;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View to edit a booking
 * 
 * @property-read Form $form
 * @property-read Registry $state
 * @property-read object $item
 * 
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An object of item
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    Registry
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Filter form
	 *
	 * @var    \JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var  \JObject
	 */
	protected $canDo;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	public function display($tpl = null): void
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->state = $this->get('State');

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
	 * @throws \Exception
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		Factory::getApplication()->getInput()->set('hidemainmenu', true);

		$user = $this->getCurrentUser();
		$isNew = ($this->item->id == 0);
		$toolbar = Toolbar::getInstance();

		$canDo = ContentHelper::getActions('com_roombooking');

		ToolbarHelper::title($isNew ? Text::_('COM_ROOMBOOKING_MANAGER_BOOKING_NEW') : Text::_('COM_ROOMBOOKING_MANAGER_BOOKING_EDIT'), 'bookmark bookings');

		// If not checked out, can save the item.
		if ($canDo->get('core.edit') || $canDo->get('core.create')) {
			$toolbar->apply('booking.apply');
		}

		$saveGroup = $toolbar->dropdownButton('save-group');

		$saveGroup->configure(
			function (Toolbar $childBar) use ($canDo, $user, $isNew): void {
				// If not checked out, can save the item.
				if ($canDo->get('core.edit') > 0) {
					$childBar->save('booking.save');

					if ($canDo->get('core.create')) {
						$childBar->save2new('booking.save2new');
					}
				}

				// If an existing item, can save to a copy.
				if (!$isNew && $canDo->get('core.create')) {
					$childBar->save2copy('booking.save2copy');
				}
			}
		);

		if (empty($this->item->id)) {
			$toolbar->cancel('booking.cancel', 'JTOOLBAR_CANCEL');
		} else {
			$toolbar->cancel('booking.cancel');

			if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit')) {
				$toolbar->versions('com_roombooking.booking', $this->item->id);
			}
		}
	}
}
