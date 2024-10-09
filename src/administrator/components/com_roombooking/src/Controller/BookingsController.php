<?php

namespace Joomla\Component\Roombooking\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Bookings list controller class.
 *
 * @since  1.0.0
 */
class BookingsController extends AdminController
{
	/**
	 * Proxy for getModel
	 * @since    1.6
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array $config
	 *
	 * @return BaseDatabaseModel|bool
	 */
	public function getModel($name = 'Booking', $prefix = 'Administrator', $config = []): BaseDatabaseModel|bool
	{
		return parent::getModel($name, $prefix, ['ignore_request' => true]);
	}
}
