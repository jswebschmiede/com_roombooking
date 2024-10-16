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

abstract class TokenHelper
{
	public static function saveToken(DatabaseInterface $db, int $bookingId, string $token, string $type, DateTime $expiresAt): bool
	{
		$query = $db->getQuery(true);

		$query->insert($db->quoteName('#__roombooking_tokens'))
			->columns($db->quoteName(['booking_id', 'token', 'type', 'expires_at']))
			->values(implode(',', [
				$db->quote($bookingId),
				$db->quote($token),
				$db->quote($type),
				$db->quote($expiresAt->format('Y-m-d H:i:s'))
			]));

		$db->setQuery($query);
		return $db->execute();
	}

	public static function getValidTokenInfo(DatabaseInterface $db, string $token): ?object
	{
		$query = $db->getQuery(true);

		$query->select(['booking_id', 'type'])
			->from($db->quoteName('#__roombooking_tokens'))
			->where($db->quoteName('token') . ' = :token')
			->bind(':token', $token, ParameterType::STRING)
			->where($db->quoteName('expires_at') . ' > NOW()');

		$db->setQuery($query);
		return $db->loadObject();
	}

	public static function getTokenByBookingId(DatabaseInterface $db, int $bookingId, string $type): ?string
	{
		$query = $db->getQuery(true);

		$query->select('token')
			->from($db->quoteName('#__roombooking_tokens'))
			->where($db->quoteName('booking_id') . ' = ' . $db->quote($bookingId))
			->where($db->quoteName('type') . ' = ' . $db->quote($type))
			->where($db->quoteName('expires_at') . ' > NOW()')
			->order($db->quoteName('created_at') . ' DESC')
			->setLimit(1);

		$db->setQuery($query);
		return $db->loadResult();
	}

	public static function deleteToken(DatabaseInterface $db, string $token): bool
	{
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__roombooking_tokens'))
			->where($db->quoteName('token') . ' = :token')
			->bind(':token', $token, ParameterType::STRING);

		$db->setQuery($query);
		return $db->execute();
	}

	public static function generateToken(): string
	{
		return bin2hex(random_bytes(32));
	}
}
