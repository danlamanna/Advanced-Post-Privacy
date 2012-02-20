<div class="wrap">
	<div id="icon-edit-pages" class="icon32"></div>
	<h2>Advanced Post Privacy Settings</h2>

	<?php $exclusionModuleDirs = advancedPostPrivacy::getExclusionModuleData('directory'); ?>

	<?php foreach ($exclusionModuleDirs as $exclusionModuleDir): ?>
		<?php include_once(APP_PATH . '/' . $exclusionModuleDir . '/settings.php'); ?>
	<?php endforeach; ?>
</div> <!-- .wrap -->