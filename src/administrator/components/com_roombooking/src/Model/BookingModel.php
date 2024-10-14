<?php

namespace Joomla\Component\Roombooking\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\FormFactoryInterface;

/**
 * Booking model.
 *
 * @since  1.0.0
 */
class BookingModel extends AdminModel
{
	/**
	 * The type alias for this content type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $typeAlias = 'com_roombooking.booking';

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = [], $loadData = true): bool|Form
	{
		// Get the form.
		$form = $this->loadForm(
			'com_roombooking.booking',
			'booking',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Summary of getTable
	 * @param mixed $name
	 * @param mixed $prefix
	 * @param mixed $options
	 * @throws \Exception
	 * @return bool|Table
	 */
	public function getTable($name = '', $prefix = '', $options = []): bool|Table
	{
		$name = 'booking';
		$prefix = 'Table';

		if ($table = $this->_createTable($name, $prefix, $options)) {
			return $table;
		}

		throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData(): mixed
	{
		$app = Factory::getApplication();
		$data = $app->getUserState('com_roombooking.edit.booking.data', []);

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table): void
	{
		$date = Factory::getDate();

		if (empty($table->id)) {
			$table->created = $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = $this->getDatabase();
				$query = $db->getQuery(true)
					->select('MAX(' . $db->quoteName('ordering') . ')')
					->from($db->quoteName('#__roombooking_bookings'));

				$db->setQuery($query);
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Generate a new name
	 *
	 * @param   string  $name  The name to increment
	 *
	 * @return  string  The new name
	 *
	 * @since   1.0.0
	 */
	protected function generateNewName(string $name): string
	{
		return StringHelper::increment($name, 'dash');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.0.0
	 */
	public function save($data): bool
	{
		$db = $this->getDatabase();
		$input = Factory::getApplication()->getInput();
		// Alter the name for save as copy
		if ($input->get('task') == 'save2copy') {
			/** @var \Joomla\Component\Roombooking\Administrator\Table\RoomTable $origTable */
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['name'] == $origTable->name) {
				$data['name'] = $this->generateNewName($data['name']);
			}

			$data['state'] = 0;
		}

		// Save the main booking data
		if (!parent::save($data)) {
			return false;
		}

		// Get the booking ID
		$bookingId = (int) $this->getState($this->getName() . '.id');

		// Handle booking dates
		if (isset($data['booking_dates']) && is_array($data['booking_dates'])) {
			// Delete existing dates for this booking
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__roombooking_booking_dates'))
				->where($db->quoteName('booking_id') . ' = :bookingId')
				->bind(':bookingId', $bookingId, ParameterType::INTEGER);
			$db->setQuery($query)->execute();

			// Insert new dates
			foreach ($data['booking_dates'] as $dateEntry) {
				if (!empty($dateEntry['booking_date'])) {
					$date = $dateEntry['booking_date'];
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__roombooking_booking_dates'))
						->columns($db->quoteName(['booking_id', 'booking_date']))
						->values(':bookingId, :bookingDate')
						->bind(':bookingId', $bookingId, ParameterType::INTEGER)
						->bind(':bookingDate', $date);

					$db->setQuery($query)->execute();
				}
			}
		}

		return true;
	}

	/**
	 * Summary of getItem with booking dates
	 * @param mixed $pk
	 * @return mixed
	 */
	public function getItem($pk = null): mixed
	{
		$item = parent::getItem($pk);

		if ($item) {
			// Load the booking dates
			$db = $this->getDatabase();
			$query = $db->getQuery(true)
				->select($db->quoteName('booking_date'))
				->from($db->quoteName('#__roombooking_booking_dates'))
				->where($db->quoteName('booking_id') . ' = :bookingId')
				->bind(':bookingId', $item->id, ParameterType::INTEGER)
				->order($db->quoteName('booking_date') . ' ASC');

			$db->setQuery($query);
			$dates = $db->loadColumn();

			$item->booking_dates = array_map(function ($date) {
				return ['booking_date' => $date];
			}, $dates);
		}

		return $item;
	}

	/**
	 * Summary of getFormFactory
	 * @return FormFactoryInterface
	 */
	public function getFormFactory(): FormFactoryInterface
	{
		return parent::getFormFactory();
	}
}
