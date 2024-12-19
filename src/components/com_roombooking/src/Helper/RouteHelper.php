<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\Helper;

use Joomla\CMS\Language\Multilanguage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Roombooking Component Route Helper.
 *
 * @since  1.0.0
 */
abstract class RouteHelper
{
    /**
     * Get the room route.
     *
     * @param   integer  $id        The route of the content item.
     * @param   string   $language  The language code.
     * @param   string   $layout    The layout value.
     *
     * @return  string  The room route.
     *
     * @since   1.0.0
     */
    public static function getRoomRoute($id, $language = null, $layout = null): string
    {
        // Create the link
        $link = 'index.php?option=com_roombooking&view=room&id=' . $id;

        if (!empty($language) && $language !== '*' && Multilanguage::isEnabled()) {
            $link .= '&lang=' . $language;
        }

        if ($layout) {
            $link .= '&layout=' . $layout;
        }

        return $link;
    }
}
