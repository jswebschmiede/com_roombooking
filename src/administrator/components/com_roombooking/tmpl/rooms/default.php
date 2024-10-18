<?php

/**
 * @package     com_roombooking
 * @version     1.0.0
 * @copyright   Copyright (C) 2024. All rights reserved.
 * @license     MIT License (MIT) see LICENSE.txt
 * @author      Jörg Schöneburg <info@joerg-schoeneburg.de> - https://joerg-schoeneburg.de
 */

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Multilanguage;

defined('_JEXEC') or die;

/** @var \Joomla\Component\Roombooking\Administrator\View\Rooms\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect');

$user = $this->getCurrentUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder && !empty($this->items)) {
	$saveOrderingUrl = 'index.php?option=com_roombooking&task=rooms.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$editIcon = '<span class="fa fa-pen-square mr-2" aria-hidden="true"></span>';

?>

<form action="<?php echo Route::_('index.php?option=com_roombooking&view=rooms'); ?>" method="post" name="adminForm"
	id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
				?>

				<?php if (empty($this->items)): ?>
					<div class="alert alert-info">
						<span class="fa fa-info-circle" aria-hidden="true"></span>
						<span class="sr-only"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table itemList" id="roomsList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_ROOMBOOKING_ROOMS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>

								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-sort'); ?>
								</th>

								<th scope="col" class="w-5 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
								</th>

								<th scope="col" class="w-15">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
								</th>

								<th class="w-5">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_ROOMBOOKING_HEADING_BACKEND_LIST_ROOMS_BOOKINGS', 'bookings', $listDirn, $listOrder); ?>
								</th>

								<th class="w-5">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_ROOMBOOKING_HEADING_BACKEND_LIST_ROOMS_CAPACITY', 'a.capacity', $listDirn, $listOrder); ?>
								</th>

								<th scope="col" class="w-10">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_CREATED_DATE', 'a.created', $listDirn, $listOrder); ?>
								</th>

								<?php if (Multilanguage::isEnabled()): ?>
									<th scope="col" class="w-10 d-none d-md-table-cell">
										<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
									</th>
								<?php endif; ?>

								<th class="w-10">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_FIELD_CREATED_BY_LABEL', 'a.created_by', $listDirn, $listOrder); ?>
								</th>

								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder): ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>"
								data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true" <?php endif; ?>>
							<?php foreach ($this->items as $i => $item):
								$ordering = ($listOrder == 'ordering');
								$canCreate = $user->authorise('core.create', 'com_roombooking');
								$canEdit = $user->authorise('core.edit', 'com_roombooking');
								$canChange = $user->authorise('core.edit.state', 'com_roombooking');
								?>
								<tr class="row<?php echo $i % 2; ?>" data-draggable-group="0">
									<td class="text-center">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>

									<td class="text-center d-none d-md-table-cell">
										<?php
										$iconClass = '';

										if (!$canChange) {
											$iconClass = ' inactive';
										} elseif (!$saveOrder) {
											$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler <?php echo $iconClass ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder): ?>
											<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>"
												class="width-20 text-area-order hidden">
										<?php endif; ?>
									</td>

									<td class="article-status text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'rooms.', $canChange, 'cb'); ?>
									</td>

									<th scope="row" class="has-context">
										<div class="break-word">
											<?php if ($canEdit): ?>
												<a class="hasTooltip d-inline-flex align-items-center gap-1"
													href="<?php echo Route::_('index.php?option=com_roombooking&task=room.edit&id=' . $item->id); ?>"
													title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>"
													data-bs-placement="top">
													<?php echo $editIcon; ?>
													<?php echo $this->escape($item->name); ?>
												</a>
											<?php else: ?>
												<?php echo $this->escape($item->name); ?>
											<?php endif; ?>
											<div class="small break-word">
												<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
											</div>
										</div>
									</th>

									<td>
										<?php echo $item->bookings; ?>
									</td>

									<td>
										<?php echo $item->capacity; ?>
									</td>

									<td class="created small">
										<?php echo HTMLHelper::_('date', $item->created, Text::_('DATE_FORMAT_LC2')); ?>
									</td>

									<?php if (Multilanguage::isEnabled()): ?>
										<td class="small d-none d-md-table-cell">
											<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
										</td>
									<?php endif; ?>

									<td class="small">
										<?php echo $item->author; ?>
									</td>

									<td class="id d-none d-md-table-cell">
										<?php echo $item->id; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>

				<?php echo $this->pagination->getListFooter(); ?>

				<?php // Load the batch processing form. ?>
				<?php
				if (
					$user->authorise('core.create', 'com_roombooking')
					&& $user->authorise('core.edit', 'com_roombooking')
					&& $user->authorise('core.edit.state', 'com_roombooking')
				): ?>
					<template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>