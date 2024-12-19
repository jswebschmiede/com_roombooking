<?php

/**
 * @package     Joomla.Site
 * @package     com_roombooking
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Roombooking\Site\Service;

use Joomla\CMS\Menu\AbstractMenu;
use Joomla\Database\ParameterType;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\RouterViewConfiguration;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Routing class of com_content
 *
 * @since  3.3
 */
class Router extends RouterView
{
    /**
     * Flag to remove IDs
     *
     * @var    boolean
     */
    protected bool $noIDs = false;

    /**
     * The category factory
     *
     * @var CategoryFactoryInterface
     *
     * @since   4.0.0
     */
    private ?CategoryFactoryInterface $categoryFactory = null;

    /**
     * The db
     *
     * @var DatabaseInterface
     *
     * @since  4.0.0
     */
    private ?DatabaseInterface $db = null;

    /**
     * Roombooking Component router constructor
     *
     * @param   SiteApplication           $app              The application object
     * @param   AbstractMenu              $menu             The menu object to work with
     * @param   CategoryFactoryInterface  $categoryFactory  The category object
     * @param   DatabaseInterface         $db               The database object
     */
    public function __construct(SiteApplication $app, AbstractMenu $menu, ?CategoryFactoryInterface $categoryFactory, ?DatabaseInterface $db)
    {
        $this->categoryFactory = $categoryFactory;
        $this->db = $db;

        $params = ComponentHelper::getParams('com_roombooking');
        $this->noIDs = (bool) $params->get('sef_ids');

        $rooms = new RouterViewConfiguration('rooms');
        $rooms->setKey('id');
        $this->registerView($rooms);

        $room = new RouterViewConfiguration('room');
        $room->setKey('id');
        $this->registerView($room);


        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    /**
     * Method to get the segment(s) for an article
     *
     * @param   string  $id     ID of the article to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     */
    public function getRoomSegment($id, $query)
    {
        if (!strpos($id, ':')) {
            $id = (int) $id;
            $alias = $this->getAlias($id);
            if ($alias) {
                $id .= ':' . $alias;
            }
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);
            return [$void => $segment];
        }

        return [(int) $id => $id];
    }

    /**
     * Method to get the segment(s) for a form
     *
     * @param   string  $id     ID of the article form to retrieve the segments for
     * @param   array   $query  The request that is built right now
     *
     * @return  array|string  The segments of this item
     *
     * @since   3.7.3
     */
    public function getFormSegment($id, $query): array|string
    {
        return $this->getRoomSegment($id, $query);
    }

    /**
     * Method to get the segment(s) for an article
     *
     * @param   string  $segment  Segment of the article to retrieve the ID for
     * @param   array   $query    The request that is parsed right now
     *
     * @return  mixed   The id of this item or false
     */
    public function getRoomId($segment, $query)
    {
        if ($this->noIDs) {
            $dbquery = $this->db->getQuery(true);
            $dbquery->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__roombooking_rooms'))
                ->where(
                    [
                        $this->db->quoteName('alias') . ' = :alias',
                    ]
                )
                ->bind(':alias', $segment);
            $this->db->setQuery($dbquery);

            return (int) $this->db->loadResult();
        }

        return (int) $segment;
    }

    /**
     * Get the alias for an ID
     *
     * @param   int  $id  The ID to get the alias for
     *
     * @return  string  The alias
     */
    private function getAlias(int $id): string
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('alias'))
            ->from($this->db->quoteName('#__roombooking_rooms'))
            ->where($this->db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $this->db->setQuery($query);

        try {
            return $this->db->loadResult() ?: '';
        } catch (\RuntimeException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode());
        }
    }
}
