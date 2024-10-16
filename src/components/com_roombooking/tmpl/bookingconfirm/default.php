<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

defined('_JEXEC') or die;

/** @var \Joomla\Component\Roombooking\Site\View\BookingConfirm\HtmlView $this */
?>

<h2>Booking Confirmation</h2>

<p class="alert alert-<?php echo $this->type; ?>"><?php echo $this->message; ?></p>

<a href="/">Zurück zur Startseite</a>