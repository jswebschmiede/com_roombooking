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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML Room View class for the Roombooking component
 *
 * @property-read Form $form
 * @property-read Registry $state
 * @property-read Registry $params
 * @property-read \JObject $item 
 * @property-read string $bookingDatesJson
 * @property-read float $vatRate
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
	 * @var \Joomla\CMS\Form\Form
	 */
	protected $form;

	/**
	 * @var string
	 */
	protected $bookingDatesJson;

	/**
	 * @var float
	 */
	protected $vatRate;

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
		$componentParams = ComponentHelper::getParams('com_roombooking');

		$this->item = $this->get('Item');
		$this->state = $this->get('State');
		$this->params = $app->getParams('com_roombooking');
		$this->bookingDatesJson = $this->get('BookingDatesJson');
		$this->vatRate = $componentParams->get('vat_rate', 19) / 100;
		$this->form = $this->getModel()->getForm();

		// Set the validation texts for the recurrence end date and booking date fields
		$this->form->setFieldAttribute('recurrence_end_date', 'data-validation-text', Text::_('COM_ROOMBOOKING_BOOKING_RECURRENCE_END_DATE_ERROR'));

		$this->form->setFieldAttribute('booking_date', 'data-validation-text', Text::_('COM_ROOMBOOKING_BOOKING_DATE_ERROR'));

		$this->form->setValue('room_id', null, $this->item->id);

		// Check for errors.
		if (\count($errors = $this->get('Errors'))) {
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		// Create a shortcut for $item.
		$item = $this->item;

		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

		parent::display($tpl);
	}
}
