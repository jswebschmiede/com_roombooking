<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

defined('_JEXEC') or die;

class MailtemplateTable extends Table
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
		$this->typeAlias = 'com_roombooking.mailtemplate';

		parent::__construct('#__roombooking_mail_templates', 'id', $db, $dispatcher);

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
		// Store the new row
		$result = parent::store($updateNulls);

		return $result;
	}
}
