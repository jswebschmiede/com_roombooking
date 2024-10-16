<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\View\BookingConfirm;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML Room View class for the Roombooking component
 *
 * @property-read string $message
 * 
 * @since  1.0.0
 */
class HtmlView extends BaseHtmlView
{
	protected $message;
	protected $type;

	public function setData($message, $type): void
	{
		$this->message = $message;
		$this->type = $type;
	}
}
