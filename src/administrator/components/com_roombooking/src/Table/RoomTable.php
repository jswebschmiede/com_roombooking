<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Versioning\VersionableTableInterface;

defined('_JEXEC') or die;

class RoomTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since   1.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver        $db          Database connector object
	 * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * @since   1.0.0
	 */
	public function __construct(DatabaseDriver $db, DispatcherInterface $dispatcher = null)
	{
		$this->typeAlias = 'com_roombooking.room';

		parent::__construct('#__roombooking_rooms', 'id', $db, $dispatcher);

		$this->created = Factory::getDate()->toSql();
		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 *
	 * @see     Table::check
	 * @since   1.0.0
	 */
	public function check(): bool
	{
		try {
			parent::check();
		} catch (\Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		// Set name
		$this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);

		// Set alias
		if (trim($this->alias) == '') {
			$this->alias = $this->name;
		}

		$this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

		// Set alias if empty
		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		// Set created date if not set.
		if (!(int) $this->created) {
			$this->created = Factory::getDate()->toSql();
		}

		// Set ordering
		if ($this->state < 0) {
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		} elseif (empty($this->ordering)) {
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder($this->_db->quoteName('state') . ' >= 0');
		}

		// Set modified to created if not set
		if (!$this->modified) {
			$this->modified = $this->created;
		}

		// Set modified_by to created_by if not set
		if (empty($this->modified_by)) {
			$this->modified_by = $this->created_by;
		}

		return true;
	}

	/**
	 * Method to store a row
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success, false on failure.
	 */
	public function store($updateNulls = true): bool
	{
		$db = $this->getDbo();

		// Verify that the alias is unique
		$table = new self($db, $this->getDispatcher());

		if ($table->load(['alias' => $this->alias]) && ($table->id != $this->id || $this->id == 0)) {
			$this->setError(Text::_('COM_ROOMBOOKING_ERROR_UNIQUE_ALIAS'));

			return false;
		}

		// Store the new row
		$result = parent::store($updateNulls);

		// Need to reorder ?
		if ($result && !empty($this->_reorderConditions)) {
			// Reorder the oldrow
			$this->reorder($this->_db->quoteName('state') . ' >= 0');
		}

		return $result;
	}

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string|null  The alias as described above
	 *
	 * @since   1.0.0
	 */
	public function getTypeAlias(): string|null
	{
		return $this->typeAlias;
	}
}
