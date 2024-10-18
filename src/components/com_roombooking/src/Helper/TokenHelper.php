<?php


/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\Helper;

use DateTime;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * TokenHelper class
 * 
 * @since 1.0.0
 */
abstract class TokenHelper
{
	/**
	 * Save the token in the database
	 * 
	 * @param DatabaseInterface $db
	 * @param int $bookingId
	 * @param string $token
	 * @param DateTime $expiresAt
	 * @return bool
	 */
	public static function saveToken(DatabaseInterface $db, int $bookingId, string $token, DateTime $expiresAt): bool
	{
		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__roombooking_tokens'))
			->columns($db->quoteName(['booking_id', 'token', 'expires_at']))
			->values(implode(',', [
				$db->quote($bookingId),
				$db->quote($token),
				$db->quote($expiresAt->format('Y-m-d H:i:s'))
			]));

		$db->setQuery($query);
		return $db->execute();
	}

	/**
	 * Get the valid token info
	 * 
	 * @param DatabaseInterface $db
	 * @param string $token
	 * @return object|null
	 */
	public static function getValidTokenInfo(DatabaseInterface $db, string $token): ?object
	{
		$query = $db->getQuery(true);

		$query->select(['booking_id'])
			->from($db->quoteName('#__roombooking_tokens'))
			->where($db->quoteName('token') . ' = :token')
			->bind(':token', $token, ParameterType::STRING)
			->where($db->quoteName('expires_at') . ' > NOW()');

		$db->setQuery($query);
		return $db->loadObject();
	}

	/**
	 * Get the token by booking id
	 * 
	 * @param DatabaseInterface $db
	 * @param int $bookingId
	 * @return string|null
	 */
	public static function getTokenByBookingId(DatabaseInterface $db, int $bookingId): ?string
	{
		$query = $db->getQuery(true);

		$query->select('token')
			->from($db->quoteName('#__roombooking_tokens'))
			->where($db->quoteName('booking_id') . ' = ' . $db->quote($bookingId))
			->where($db->quoteName('expires_at') . ' > NOW()')
			->order($db->quoteName('created_at') . ' DESC')
			->setLimit(1);

		$db->setQuery($query);
		return $db->loadResult();
	}

	/**
	 * Delete the token
	 * 
	 * @param DatabaseInterface $db
	 * @param string $token
	 * @return bool
	 */
	public static function deleteToken(DatabaseInterface $db, string $token): bool
	{
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__roombooking_tokens'))
			->where($db->quoteName('token') . ' = :token')
			->bind(':token', $token, ParameterType::STRING);

		$db->setQuery($query);
		return $db->execute();
	}

	/**
	 * Generate a random token
	 * 
	 * @return string
	 */
	public static function generateToken(): string
	{
		return bin2hex(random_bytes(32));
	}
}
