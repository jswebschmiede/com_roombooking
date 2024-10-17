<?php

namespace Joomla\Component\Roombooking\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormFactoryInterface;

/**
 * Methods supporting a list of booking records.
 *
 * @since  1.0.0
 */
class BookingsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id',
				'a.id',
				'room_id',
				'a.room_id',
				'title',
				'a.title',
				'booking_date',
				'a.booking_date',
				'payment_status',
				'a.payment_status',
				'recurring',
				'a.recurring',
				'recurrence_type',
				'a.recurrence_type',
				'recurrence_end_date',
				'a.recurrence_end_date',
				'state',
				'a.state',
				'created',
				'a.created',
				'ordering',
				'a.ordering',
				'published',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'a.name', $direction = 'asc')
	{
		// Load the parameters.
		$this->setState('params', ComponentHelper::getParams('com_roombooking'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Summary of getStoreId
	 * @param string $id
	 * @return string
	 */
	protected function getStoreId($id = ''): string
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.room_id');
		$id .= ':' . $this->getState('filter.recurring');
		$id .= ':' . $this->getState('filter.payment_status');

		return parent::getStoreId($id);
	}

	/**
	 * Summary of getListQuery
	 * @return DatabaseQuery
	 */
	protected function getListQuery(): DatabaseQuery
	{
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				[
					$db->quoteName('a.id'),
					$db->quoteName('a.room_id'),
					$db->quoteName('a.name'),
					$db->quoteName('a.recurring'),
					$db->quoteName('a.recurrence_type'),
					$db->quoteName('a.recurrence_end_date'),
					$db->quoteName('a.state'),
					$db->quoteName('a.payment_status'),
					$db->quoteName('a.confirmed'),
					$db->quoteName('a.created'),
					$db->quoteName('a.ordering'),
					$db->quoteName('r.name', 'room_name'),
					'GROUP_CONCAT(' . $db->quoteName('bd.booking_date') . ' ORDER BY ' . $db->quoteName('bd.booking_date') . ' SEPARATOR ", ") AS booking_dates'
				]
			)
		);

		$query->from($db->quoteName('#__roombooking_bookings', 'a'));

		// Join with the rooms table to get the room name
		$query->join('LEFT', $db->quoteName('#__roombooking_rooms', 'r') . ' ON r.id = a.room_id');

		// Join with the booking_dates table to get the booking dates
		$query->join('LEFT', $db->quoteName('#__roombooking_booking_dates', 'bd') . ' ON bd.booking_id = a.id');

		// Filter by payment status
		$paymentStatus = $this->getState('filter.payment_status');
		if (!empty($paymentStatus)) {
			$query->where($db->quoteName('a.payment_status') . ' = :paymentStatus')
				->bind(':paymentStatus', $paymentStatus, ParameterType::STRING);
		}

		// Filter by recurring status
		$recurring = $this->getState('filter.recurring');
		if (is_numeric($recurring)) {
			$query->where($db->quoteName('a.recurring') . ' = :recurring')
				->bind(':recurring', $recurring, ParameterType::INTEGER);
		}

		// Filter by published state
		$published = (string) $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where($db->quoteName('a.state') . ' = :published')
				->bind(':published', $published, ParameterType::INTEGER);
		} elseif ($published === '') {
			$query->where($db->quoteName('a.state') . ' IN (0, 1)');
		}

		// Filter by room_id
		$roomId = $this->getState('filter.room_id');
		if (is_numeric($roomId) && $roomId > 0) {
			$query->where($db->quoteName('a.room_id') . ' = :roomId')
				->bind(':roomId', $roomId, ParameterType::INTEGER);
		}

		// Search filter
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$searchId = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :searchId')
					->bind(':searchId', $searchId, ParameterType::INTEGER);
			} else {
				$search = '%' . str_replace(' ', '%', trim($search)) . '%';
				$query->where('(' . $db->quoteName('a.name') . ' LIKE :search)')
					->bind(':search', $search);
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.name');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol === 'a.ordering') {
			$ordering = $db->quoteName('a.ordering') . ' ' . $db->escape($orderDirn);
		} else {
			$ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
		}

		$query->order($ordering);

		$query->group($db->quoteName('a.id'));

		return $query;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Room', $prefix = 'Administrator', $config = []): Table
	{
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Summary of getItems
	 * @return array
	 */
	public function getItems(): array
	{
		$items = parent::getItems();

		foreach ($items as &$item) {
			if (!empty($item->booking_dates)) {
				$item->booking_dates = explode(', ', $item->booking_dates);
			} else {
				$item->booking_dates = [];
			}
		}

		return $items;
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