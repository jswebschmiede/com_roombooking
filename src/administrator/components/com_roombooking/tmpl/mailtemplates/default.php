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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Multilanguage;

defined('_JEXEC') or die;

/** @var \Joomla\Component\Roombooking\Administrator\View\Mailtemplates\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
	->useScript('multiselect');

$user = $this->getCurrentUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 'a.id';
$editIcon = '<span class="fa fa-pen-square mr-2" aria-hidden="true"></span>';
?>

<form action="<?php echo Route::_('index.php?option=com_roombooking&view=mailtemplates'); ?>" method="post"
	name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<table class="table itemList" id="mailtemplatesList">
					<caption class="visually-hidden">
						<?php echo Text::_('COM_ROOMBOOKING_MAILTEMPLATES_TABLE_CAPTION'); ?>
					</caption>
					<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>

							<th scope="col" class="w-5 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>

							<th scope="col" class="w-15">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
							</th>

							<?php if (Multilanguage::isEnabled()): ?>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>

							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->items as $i => $item):
							$canEdit = $user->authorise('core.edit', 'com_roombooking');
							$canChange = $user->authorise('core.edit.state', 'com_roombooking');
							?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>

								<td class="article-status text-center">
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'mailtemplates.', $canChange, 'cb'); ?>
								</td>

								<th scope="row" class="has-context">
									<div class="break-word">
										<?php if ($canEdit): ?>
											<a class="hasTooltip d-inline-flex align-items-center gap-1"
												href="<?php echo Route::_('index.php?option=com_roombooking&task=mailtemplate.edit&id=' . $item->id); ?>"
												title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>"
												data-bs-placement="top">
												<?php echo $editIcon; ?>
												<?php echo $this->escape($item->name); ?>
											</a>
										<?php else: ?>
											<?php echo $this->escape($item->name); ?>
										<?php endif; ?>
									</div>
								</th>

								<?php if (Multilanguage::isEnabled()): ?>
									<td class="small d-none d-md-table-cell">
										<?php echo LayoutHelper::render('joomla.content.language', $item); ?>
									</td>
								<?php endif; ?>

								<td class="id d-none d-md-table-cell">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>