<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

namespace Joomla\Component\Roombooking\Site\Model;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This models supports retrieving lists of boilerplates.
 *
 * @since  1.6
 */
class RoomsModel extends ListModel
{
    /**
     * Model context string.
     *
     * @var     string
     */
    public $_context = 'com_roombooking.rooms';

    /**
     * The boilerplate context (allows other extensions to derived from this model).
     *
     * @var     string
     */
    protected $_extension = 'com_roombooking';

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   1.0.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'a.id',
                'name',
                'a.name',
                'alias',
                'a.alias',
                'state',
                'a.state',
                'ordering',
                'a.ordering',
                'language',
                'a.language',
                'published',
                'created',
                'a.created'
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.extension');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.parentId');
        $id .= ':' . $this->getState('list.limit');
        $id .= ':' . $this->getState('list.start');
        $id .= ':' . $this->getState('list.ordering');

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   The field to order on.
     * @param   string  $direction  The direction to order on.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'ordering', $direction = 'ASC'): void
    {
        $app = Factory::getApplication();
        $input = $app->input;

        // List state information
        $value = $input->get('limit', $app->get('list_limit', 0), 'uint');
        $this->setState('list.limit', $value);

        $value = $input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $value = $input->get('filter_tag', 0, 'uint');
        $this->setState('filter.tag', $value);

        $orderCol = $input->get('filter_order', 'a.ordering');

        if (!\in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'a.ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $input->get('filter_order_Dir', 'ASC');

        if (!\in_array(strtoupper($listOrder), ['ASC', 'DESC', ''])) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $user = $this->getCurrentUser();

        if ((!$user->authorise('core.edit.state', 'com_roombooking')) && (!$user->authorise('core.edit', 'com_roombooking'))) {
            // Filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);
        }
        $params = $app->getParams();
        $this->setState('params', $params);
        $user = $this->getCurrentUser();

        if ((!$user->authorise('core.edit.state', 'com_roombooking')) && (!$user->authorise('core.edit', 'com_roombooking'))) {
            // Filter on published for those who do not have edit or edit.state rights.
            $this->setState('filter.published', ContentComponent::CONDITION_PUBLISHED);
        }

        $this->setState('filter.language', Multilanguage::isEnabled());

        // Process show_noauth parameter
        if ((!$params->get('show_noauth')) || (!ComponentHelper::getParams('com_roombooking')->get('show_noauth'))) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }

        $this->setState('layout', $input->getString('layout'));
        $this->setState('filter.language', Multilanguage::isEnabled());

        // Process show_noauth parameter
        if ((!$params->get('show_noauth')) || (!ComponentHelper::getParams('com_roombooking')->get('show_noauth'))) {
            $this->setState('filter.access', true);
        } else {
            $this->setState('filter.access', false);
        }

        $this->setState('layout', $input->getString('layout'));
    }

    /**
     * Summary of getFormFactory
     * @return FormFactoryInterface
     */
    public function getFormFactory(): FormFactoryInterface
    {
        return parent::getFormFactory();
    }

    protected function getListQuery(): DatabaseQuery
    {
        $db = $this->getDatabase();

        /** @var \Joomla\Database\DatabaseQuery $query */
        $query = $db->getQuery(true);

        $query->select($this->getState('list.select', [
            $db->quoteName('a.id'),
            $db->quoteName('a.name'),
            $db->quoteName('a.alias'),
            $db->quoteName('a.state'),
            $db->quoteName('a.ordering'),
            $db->quoteName('a.language'),
            $db->quoteName('a.metakey'),
            $db->quoteName('a.created_by'),
            $db->quoteName('a.created'),
            $db->quoteName('a.modified'),
            $db->quoteName('a.modified_by'),
            $db->quoteName('a.description'),
            $db->quoteName('l.title', 'language_title'),
            $db->quoteName('l.image', 'language_image'),
            $db->quoteName('u.name', 'author')
        ]));
        $query->from($db->quoteName('#__roombooking_rooms', 'a'))
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'));

        // Filter by published state
        $condition = (int) $this->getState('filter.published');

        $query->where($db->quoteName('a.state') . ' = :condition')
            ->bind(':condition', $condition, ParameterType::INTEGER);

        // Add the list ordering clause.
        $orderCol = $this->getState('list.ordering', 'a.name');
        $orderDirn = $this->getState('list.direction', 'ASC');

        $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

        $query->order($ordering);

        return $query;
    }
}