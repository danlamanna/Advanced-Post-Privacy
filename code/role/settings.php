<div id="dashboard_right_now" class="postbox" style="margin-top:15px;width:45%;float:left;">
		<h3 class="hndle" style="padding:10px;">
			<span>Role Based Exclusions</span>
		</h3>

		<div class="inside">
		<?php $appRoles = appHelper::getAllRoles(); ?>

			<div id="role-based-exclusion">
				<a id="add-role-exclusion" href="#">
					<img class="plus-add-new" src="<?php echo APP_DESIGN_URL . '/img/add.png'; ?>" title="Add New" />
				</a>

				<div class="role-exclusion-container">
					<?php foreach ($appRoles as $roleName => $roleLabel): ?>
						<?php if ($roleExclusions = roleExclusions::getExclusion($roleName)): ?>
							<strong><?php echo $roleLabel['name']; ?></strong>
							
							<ul>
								<?php foreach ($roleExclusions as $key => $value): ?>
									<?php $postTypeLabels = get_post_type_object($value); ?>
									<?php $postTypeLabels = $postTypeLabels->labels; ?>
									<li class="exclusion-role-<?php echo $roleName; ?>">
										<?php echo $roleLabel['name']; ?>s are bypassing private <?php echo $postTypeLabels->name; ?>.
										<a id="remove-role-exclusion-<?php echo $key ?>" class="remove-role-exclusion" href="#">
											<img class="x-remove-symbol" src="<?php echo APP_DESIGN_URL . '/img/remove.png'; ?>" title="Remove Exclusion" />
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					<?php endforeach; ?>
	
				</div>
			</div>
		</div>
</div>