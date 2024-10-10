<?php


/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\Helper;

use NumberFormatter;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Roombooking Component Helper.
 *
 * @since  1.5
 */
abstract class RoombookingHelper
{
    /**
     * Format the price.
     *
     * @param float $price The price to format.
     * @return string The formatted price.
     */
    public static function formatPrice(float $price): string
    {
        $lang = Factory::getApplication()->getLanguage();
        $locale = $lang->getTag();

        // Convert locale format from "de-DE" to "de_DE"
        $formatterLocale = str_replace('-', '_', $locale) . '@currency=EUR';
        $formatter = new NumberFormatter($formatterLocale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($price, 'EUR');
    }
}
