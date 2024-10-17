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
	 * Get the room ID from the request
	 * 
	 * @return int
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
	 * Summary of getItem
	 * 
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
					throw new \RuntimeException(Text::_('COM_ROOMBOOKING_ERROR_ROOM_NOT_FOUND'), 404);
				}

				$this->_item[$pk] = $data;

			} catch (\RuntimeException $e) {
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
	 * 
	 * @param array $data
	 * @param bool $loadData
	 * @return Form
	 * @throws \RuntimeException
	 */
	public function getForm($data = array(), $loadData = true): Form
	{
		$form = $this->loadForm(
			'com_roombooking.room',
			'room_form',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form)) {
			$errors = $this->getErrors();
			throw new \RuntimeException(implode("\n", $errors), 500);
		}

		return $form;
	}

	/**
	 * Summary of loadFormData
	 * 
	 * @return mixed
	 */
	protected function loadFormData(): mixed
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			'com_roombooking.room',
			array()
		);

		return $data;
	}

	/**
	 * Summary of getFormFactory
	 * 
	 * @return FormFactoryInterface
	 */
	public function getFormFactory(): FormFactoryInterface
	{
		return parent::getFormFactory();
	}

	/**
	 * Get the booking dates as JSON
	 * 
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

		} catch (\RuntimeException $e) {
			throw new \RuntimeException("Error in getBookingDates method: " . $e->getMessage(), 500);
		}
	}
}