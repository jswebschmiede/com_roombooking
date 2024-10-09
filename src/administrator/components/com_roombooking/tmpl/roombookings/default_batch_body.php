<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Multilanguage;

/** @var \Joomla\Component\Roombooking\Administrator\View\Roombookings\HtmlView $this */

$state = $this->getState();
$published = (int) $state->get('filter.published');
?>

<div class="p-3">
    <div class="row">
        <?php if (Multilanguage::isEnabled()): ?>
                <div class="form-group col-md-6">
                    <div class="controls">
                        <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
                    </div>
                </div>
        <?php endif; ?>
    </div>
    <div class="row">
        <?php if ($published >= 0): ?>
                <div class="form-group col-md-6">
                    <div class="controls">
                        <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => 'com_roombooking']); ?>
                    </div>
                </div>
        <?php endif; ?>
    </div>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="roombooking.batch" class="ms-auto">
        <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
    </joomla-toolbar-button>
</div>