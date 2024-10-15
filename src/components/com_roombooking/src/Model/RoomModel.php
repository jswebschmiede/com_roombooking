<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormFactoryInterface;

defined('_JEXEC') or die;

/**
 * Methods supporting a single room record.
 *
 * @since  1.0.0
 */
class RoomModel extends FormModel
{
	/**
	 * Model context string.
	 *
	 * @var string
	 */
	protected $_context = 'com_roombooking.room';

	/**
	 * Array to store the reccurence dates
	 * @var array
	 */
	private array $_reccurenceDates = [];

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function populateState(): void
	{
		// Load state from the request.
		$pk = $this->getId();
		$this->setState('room.id', $pk);
		// Load the parameters.
		$this->setState('params', ComponentHelper::getParams('com_roombooking'));
	}


	/**
	 * @return int
	 * @throws \Exception
	 */
	private function getId(): int
	{
		$app = Factory::getApplication();

		$id = $app->input->getInt('id');
		$params = $app->getParams();

		$paramId = $params->get('id');
		if ($paramId && $id === null) {
			return (int) $paramId;
		}

		return (int) $id;
	}

	/**
	 * @param int|null $pk
	 * @return object|bool
	 * @since 1.0.0
	 */
	public function getItem($pk = null): object|bool
	{
		$pk = (int) ($pk ?: $this->getState('room.id'));

		if ($this->_item === null) {
			$this->_item = [];
		}

		if (!isset($this->_item[$pk])) {
			try {
				$db = $this->getDatabase();
				$query = $db->getQuery(true);

				$query->select($this->getState(
					'item.select',
					[
						$db->quoteName('a.id'),
						$db->quoteName('a.state'),
						$db->quoteName('a.ordering'),
						$db->quoteName('a.name'),
						$db->quoteName('a.alias'),
						$db->quoteName('a.description'),
						$db->quoteName('a.short_description'),
						$db->quoteName('a.image'),
						$db->quoteName('a.size'),
						$db->quoteName('a.price'),
						$db->quoteName('a.capacity'),
						$db->quoteName('a.created'),
						$db->quoteName('a.created_by'),
						$db->quoteName('a.modified'),
						$db->quoteName('a.modified_by'),
						$db->quoteName('a.language'),
						$db->quoteName('l.title', 'language_title'),
						$db->quoteName('l.image', 'language_image'),
						$db->quoteName('u.name', 'author'),
					]
				))
					->from($db->quoteName('#__roombooking_rooms', 'a'))
					->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'))
					->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
					->where($db->quoteName('a.id') . ' = :id')
					->bind(':id', $pk, ParameterType::INTEGER);

				$query->where($db->quoteName('a.state') . ' = 1');
				$query->order($db->quoteName('a.ordering') . ' ASC');

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data)) {
					throw new \Exception(Text::_('COM_ROOMBOOKING_ERROR_ROOM_NOT_FOUND'), 404);
				}

				$this->_item[$pk] = $data;

			} catch (\Exception $e) {
				if ($e->getCode() == 404) {
					// Need to go through the error handler to allow Redirect to work.
					throw $e;
				}

				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Summary of getForm
	 * @param array $data
	 * @param bool $loadData
	 * @return Form
	 * @throws \Exception
	 */
	public function getForm($data = array(), $loadData = true): Form
	{
		$form = $this->loadForm(
			'com_roombooking.booking',   // just a unique name to identify the form
			'booking_form',              // the filename of the XML form definition
			// Joomla will look in the site/forms folder for this file
			array(
				'control' => 'jform',    // the name of the array for the POST parameters
				'load_data' => $loadData // if set to true, then there will be a callback to 
				// loadFormData to supply the data
			)
		);

		if (empty($form)) {
			$errors = $this->getErrors();
			throw new \Exception(implode("\n", $errors), 500);
		}

		return $form;
	}

	/**
	 * Summary of loadFormData
	 * @return mixed
	 */
	protected function loadFormData(): mixed
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			'com_roombooking.booking',  // a unique name to identify the data in the session
			array("email" => ".@.") // prefill data if no data found in session
		);

		return $data;
	}

	/**
	 * Summary of getFormFactory
	 * @return FormFactoryInterface
	 */
	public function getFormFactory(): FormFactoryInterface
	{
		return parent::getFormFactory();
	}

	/**
	 * Get the booking dates as JSON
	 * @return string
	 */
	public function getBookingDatesJson(): string
	{
		$roomId = $this->getState('room.id');

		try {
			$db = $this->getDatabase();
			$query = $db->getQuery(true);

			$query
				->select($db->quoteName('bd.booking_date', 'start'))
				->from($db->quoteName('#__roombooking_booking_dates', 'bd'))
				->join('INNER', $db->quoteName('#__roombooking_bookings', 'b') . ' ON ' . $db->quoteName('b.id') . ' = ' . $db->quoteName('bd.booking_id'))
				->where($db->quoteName('b.room_id') . ' = :room_id')
				->andWhere($db->quoteName('b.state') . ' = 1')
				->bind(':room_id', $roomId, ParameterType::INTEGER);

			$db->setQuery($query);
			$result = $db->loadAssocList();

			foreach ($result as &$item) {
				$item['start'] = new Date($item['start']);
				$item['start'] = $item['start']->format('Y-m-d');
			}

			return json_encode($result);

		} catch (\Exception $e) {
			throw new \Exception("Error in getBookingDates method: " . $e->getMessage(), 500);
		}
	}

	/**
	 * Calculate the reccrence dates based of the reccurce type
	 * @param array $data
	 * @return array
	 */
	private function calculateReccurenceDates(array $data): array
	{
		if (!$this->isRecurring($data)) {
			return [];
		}

		$recurrenceType = $data['recurrence_type'];
		$recurrenceEndDate = new \DateTime($data['recurrence_end_date']);
		$bookingDate = new \DateTime($data['booking_date']);

		$this->_reccurenceDates = [];

		switch ($recurrenceType) {
			case 'weekly':
				$interval = new \DateInterval('P1W'); // 1 Woche
				break;
			case 'biweekly':
				$interval = new \DateInterval('P2W'); // 2 Wochen
				break;
			case 'monthly':
				$interval = new \DateInterval('P1M'); // 1 Monat
				break;
			default:
				return $this->_reccurenceDates;
		}

		$currentDate = clone $bookingDate;

		while ($currentDate <= $recurrenceEndDate) {
			$this->_reccurenceDates[] = $currentDate->format('Y-m-d');
			$currentDate->add($interval);
		}

		return $this->_reccurenceDates;
	}

	/**
	 * Check if the booking is recurring
	 * @param array $data
	 * @return bool
	 */
	private function isRecurring(array $data): bool
	{
		return (bool) ($data['recurring'] == 1);
	}


	/**
	 * Save the booking form data
	 * @param array $data
	 * @return bool
	 */
	public function save(array $data): bool
	{
		$db = $this->getDatabase();
		$now = Factory::getDate()->toSql();
		$name = Text::sprintf('COM_ROOMBOOKING_BOOKING_FROM', $data['customer_name']);

		try {
			// Start transaction
			$db->transactionStart();

			// Insert into #__roombooking_bookings table
			$bookingColumns = [
				'room_id',
				'total_amount',
				'customer_name',
				'customer_address',
				'customer_email',
				'customer_phone',
				'recurring',
				'recurrence_type',
				'recurrence_end_date',
				'name',
				'created',
				'privacy_accepted'
			];

			$bookingQuery = $db->getQuery(true)
				->insert($db->quoteName('#__roombooking_bookings'))
				->columns($db->quoteName($bookingColumns))
				->values('
					:room_id, 
					:total_amount, 
					:customer_name, 
					:customer_address, 
					:customer_email, 
					:customer_phone, 
					:recurring, 
					:recurrence_type, 
					:recurrence_end_date, 
					:name, 
					:created,
					:privacy_accepted
				');

			// Calculate the total amount
			$totalAmount = number_format($data['total_amount'], 4, '.', '');

			// Format the recurrence end date
			if (!empty($data['recurrence_end_date'])) {
				$recurrenceEndDate = new Date($data['recurrence_end_date']);
				$formattedRecurrenceEndDate = $recurrenceEndDate->format('Y-m-d');
			} else {
				$formattedRecurrenceEndDate = null;
			}

			// Bind the values to the booking query
			$bookingQuery
				->bind(':room_id', $data['room_id'], ParameterType::INTEGER)
				->bind(':total_amount', $totalAmount)
				->bind(':customer_name', $data['customer_name'])
				->bind(':customer_address', $data['customer_address'])
				->bind(':customer_email', $data['customer_email'])
				->bind(':customer_phone', $data['customer_phone'])
				->bind(':recurring', $data['recurring'], ParameterType::INTEGER)
				->bind(':recurrence_type', $data['recurrence_type'])
				->bind(':recurrence_end_date', $formattedRecurrenceEndDate)
				->bind(':privacy_accepted', $data['privacy_accepted'], ParameterType::INTEGER)
				->bind(':name', $name)
				->bind(':created', $now);

			$db->setQuery($bookingQuery);

			if (!$db->execute()) {
				throw new \RuntimeException('Failed to insert booking data');
			}

			// Get the booking ID
			$bookingId = $db->insertid();

			// Insert into #__roombooking_booking_dates table
			$dateQuery = $db->getQuery(true)
				->insert($db->quoteName('#__roombooking_booking_dates'))
				->columns($db->quoteName(['booking_id', 'booking_date']));

			if ($data['recurring'] == 1) {
				$recurrenceDates = $this->calculateReccurenceDates($data);

				foreach ($recurrenceDates as $date) {
					$dateQuery->values(':booking_id, :booking_date')
						->bind(':booking_id', $bookingId, ParameterType::INTEGER)
						->bind(':booking_date', $date);

					$db->setQuery($dateQuery);
					$db->execute();
					$dateQuery->clear('values');
				}

			} else {
				// save the single booking date
				$bookingDate = new Date($data['booking_date']);
				$formattedBookingDate = $bookingDate->format('Y-m-d');

				$dateQuery->values(':booking_id, :booking_date')
					->bind(':booking_id', $bookingId, ParameterType::INTEGER)
					->bind(':booking_date', $formattedBookingDate);

				$db->setQuery($dateQuery);

				if (!$db->execute()) {
					throw new \RuntimeException('Failed to insert booking dates');
				}
			}

			// If we've made it this far without exceptions, commit the transaction
			$db->transactionCommit();

			return true;

		} catch (\RuntimeException $e) {
			// Rollback the transaction
			$db->transactionRollback();

			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}
	}
}