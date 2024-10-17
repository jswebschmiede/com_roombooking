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
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Component\Roombooking\Site\Helper\TokenHelper;

defined('_JEXEC') or die;

/**
 * Methods supporting a single room record.
 *
 * @since  1.0.0
 */
class BookingModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var string
	 */
	protected $_context = 'com_roombooking.booking';

	/**
	 * Array to store the reccurence dates
	 * 
	 * @var array
	 */
	private array $_reccurenceDates = [];

	/**
	 * Calculate the reccurence dates based of the reccurrence type
	 * 
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
	 * 
	 * @param array $data
	 * @return bool
	 */
	private function isRecurring(array $data): bool
	{
		return (bool) ($data['recurring'] == 1);
	}

	/**
	 * Save the booking form data
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function save(array $data): bool
	{
		$db = $this->getDatabase();
		$now = Factory::getDate()->toSql();
		$name = Text::sprintf('COM_ROOMBOOKING_BOOKING_FROM', $data['customer_name']);
		$app = Factory::getApplication();

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
			$this->bookingId = $db->insertid();
			$app->setUserState('com_roombooking.booking.id', $this->bookingId);

			// Insert into #__roombooking_booking_dates table
			$dateQuery = $db->getQuery(true)
				->insert($db->quoteName('#__roombooking_booking_dates'))
				->columns($db->quoteName(['booking_id', 'booking_date']));

			// Save the reccurence dates
			if ($data['recurring'] == 1) {
				$recurrenceDates = $this->calculateReccurenceDates($data);

				foreach ($recurrenceDates as $date) {
					$dateQuery->values(':booking_id, :booking_date')
						->bind(':booking_id', $this->bookingId, ParameterType::INTEGER)
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
					->bind(':booking_id', $this->bookingId, ParameterType::INTEGER)
					->bind(':booking_date', $formattedBookingDate);

				$db->setQuery($dateQuery);

				if (!$db->execute()) {
					throw new \RuntimeException('Failed to insert booking dates');
				}
			}

			// Generate and save confirmation token
			$token = TokenHelper::generateToken();
			$expiresAt = new \DateTime('+24 hours');
			TokenHelper::saveToken($db, $this->bookingId, $token, 'email_confirmation', $expiresAt);

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

	/**
	 * Get the mail template
	 * 
	 * @param string $templateType
	 * @return object
	 */
	public function getMailTemplate(string $templateType): object
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select([
			$db->quoteName('mt.id', 'id'),
			$db->quoteName('mt.subject', 'subject'),
			$db->quoteName('mt.body', 'body'),
			$db->quoteName('mt.from_email', 'from_email'),
			$db->quoteName('mt.to_email', 'to_email'),
			$db->quoteName('mt.cc', 'cc'),
			$db->quoteName('mt.bcc', 'bcc'),
			$db->quoteName('mt.language', 'language'),
			$db->quoteName('mt.state', 'state'),
		])
			->from($db->quoteName('#__roombooking_mail_templates', 'mt'))
			->where($db->quoteName('mt.template_type') . ' = :template_type')
			->bind(':template_type', $templateType, ParameterType::STRING);

		$db->setQuery($query);
		return $db->loadObject();
	}

	/**
	 * Confirm the booking
	 * 
	 * @param int $bookingId
	 * @return bool
	 */
	public function confirmBooking(int $bookingId): bool
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__roombooking_bookings'))
			->set([
				$db->quoteName('confirmed') . ' = 1',
				$db->quoteName('state') . ' = 1'
			])
			->where($db->quoteName('id') . ' = :booking_id')
			->bind(':booking_id', $bookingId, ParameterType::INTEGER);

		$db->setQuery($query);
		return $db->execute();
	}

	/**
	 * Summary of getItem
	 * 
	 * @param int $pk
	 * @return object
	 */
	public function getItem($pk = null): object
	{
		return parent::getItem($pk);
	}
}
