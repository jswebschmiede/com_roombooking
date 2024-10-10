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
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ItemModel;

defined('_JEXEC') or die;

/**
 * Methods supporting a single boilerplate record.
 *
 * @since  1.0.0
 */
class RoomModel extends ItemModel
{
	/**
	 * Model context string.
	 *
	 * @var string
	 */
	protected $_context = 'com_roombooking.room';

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
}
