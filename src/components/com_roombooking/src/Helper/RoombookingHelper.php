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
use Joomla\CMS\Language\Text;

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
     * @param bool  $includeCurrencySymbol Whether to include the currency symbol. Defaults to true.
     * @return string The formatted price.
     */
    public static function formatPrice(float $price, bool $includeCurrencySymbol = true): string
    {
        $lang = Factory::getApplication()->getLanguage();
        $locale = $lang->getTag();

        // Convert locale format from "de-DE" to "de_DE"
        $formatterLocale = str_replace('-', '_', $locale) . '@currency=EUR';
        $formatter = new NumberFormatter($formatterLocale, NumberFormatter::CURRENCY);

        if (!$includeCurrencySymbol) {
            $formatter->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
        }

        return $formatter->formatCurrency($price, 'EUR');
    }

    /**
     * Translate recurrence types
     *
     * @param string $recurrenceType
     * @return string
     */
    public static function translateRecurrenceType(string $recurrenceType): string
    {
        $translations = [
            'weekly' => Text::_('COM_ROOMBOOKING_WEEKLY'),
            'biweekly' => Text::_('COM_ROOMBOOKING_BIWEEKLY'),
            'monthly' => Text::_('COM_ROOMBOOKING_MONTHLY'),
            'none' => Text::_('COM_ROOMBOOKING_NONE')
        ];

        return $translations[$recurrenceType] ?? $recurrenceType;
    }

    /**
     * Get the mail placeholders.
     *
     * @return array
     */
    public static function getMailPlaceholders(): array
    {
        return [
            '{{booking_details}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_BOOKING_DETAILS'),
            '{{customer_name}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_CUSTOMER_NAME'),
            '{{customer_email}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_CUSTOMER_EMAIL'),
            '{{customer_phone}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_CUSTOMER_PHONE'),
            '{{customer_address}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_CUSTOMER_ADDRESS'),
            '{{booking_date}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_BOOKING_DATE'),
            '{{recurring}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_RECURRING'),
            '{{recurrence_type}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_RECURRENCE_TYPE'),
            '{{recurrence_end_date}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_RECURRENCE_END_DATE'),
            '{{total_amount}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_TOTAL_AMOUNT'),
            '{{room_id}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_ROOM_ID'),
            '{{room_name}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_ROOM_NAME'),
            '{{confirm_link}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_CONFIRM_LINK'),
            '{{cancel_link}}' => Text::_('COM_ROOMBOOKING_MAIL_PLACEHOLDER_CANCEL_LINK')
        ];
    }
}
