<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Roombooking\Site\Service;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\Rules\RulesInterface;

/**
 * Rule to process URLs without a menu item
 *
 * @since  3.4
 */
class RoombookingNomenuRules implements RulesInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var RouterView
	 * @since 3.4
	 */
	protected RouterView $router;

	/**
	 * Class constructor.
	 *
	 * @param   RouterView  $router  Router this rule belongs to
	 *
	 * @since   3.4
	 */
	public function __construct(RouterView $router)
	{
		$this->router = $router;
	}

	/**
	 * Dummy method to fulfill the interface requirements
	 *
	 * @param   array  &$query  The query array to process
	 *
	 * @return  void
	 *
	 * @since   3.4
	 * @codeCoverageIgnore
	 */
	public function preprocess(&$query): void
	{
		$test = 'Test';
	}

	/**
	 * Parse a menu-less URL
	 *
	 * @param   array  &$segments  The URL segments to parse
	 * @param   array  &$vars      The vars that result from the segments
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function parse(&$segments, &$vars): void
	{
		$vars['view'] = 'room';
		$vars['id'] = substr($segments[0], strpos($segments[0], '-') + 1);
		array_shift($segments);
		array_shift($segments);
	}

	/**
	 * Build a menu-less URL
	 *
	 * @param array &$query
	 * @param array &$segments
	 *
	 * @return void
	 *
	 * @since 3.4
	 */
	public function build(&$query, &$segments): void
	{
		// Search for a matching menu item
		$menu = $this->router->menu;
		$matchingMenuItem = $menu->getItem($query['Itemid'] ?? null);

		// If limitstart for pagination is set, unset view
		if (isset($query['limitstart'])) {
			unset($query['view']);
			return;
		}

		// If a matching menu item was found
		if (
			$matchingMenuItem &&
			isset($matchingMenuItem->query['option']) && $matchingMenuItem->query['option'] === 'com_roombooking' &&
			isset($matchingMenuItem->query['view']) && $matchingMenuItem->query['view'] === 'room'
		) {
			// Remove the slug from the query, if present
			unset($query['slug']);
			// Remove view and id from the query
			unset($query['view'], $query['id']);
			return;
		}

		// If no matching menu item was found, continue with the original logic
		if (!isset($query['view']) || (isset($query['view']) && $query['view'] !== 'room') || isset($query['format'])) {
			return;
		}

		$segments[] = $query['view'] . '-' . $query['id'];
		if (isset($query['slug'])) {
			$segments[] = $query['slug'];
			unset($query['slug']);
		}
		unset($query['view'], $query['id']);
	}
}
