<div id="dashboard_right_now" class="postbox" style="margin-top:15px;width:45%;float:left;margin-left:5%;">
		<h3 class="hndle" style="padding:10px;">
			<span>IP Address Based Exclusions</span>
		</h3>

		<div class="inside">
			<div id="ip-based-exclusion">
				<a id="add-ip-exclusion" href="#">
					<img class="plus-add-new" src="<?php echo APP_DESIGN_URL . '/img/add.png'; ?>" title="Add New" />
				</a>

				<div class="ip-exclusion-container">
					<?php foreach (ipAddressExclusions::getExclusion() as $ipAddress => $postTypeExclusions): ?>
						<strong><?php echo $ipAddress; ?></strong>
							<ul>
								<?php foreach ($postTypeExclusions as $key => $value): ?>
									<?php $postTypeLabels = appHelper::getPostTypeLabels($value); ?>
									<li class="exclusion-ip-<?php echo $ipAddress; ?>">
										<?php echo $ipAddress; ?> users are bypassing private <?php echo $postTypeLabels->name; ?>.
										<a id="remove-ip-exclusion-<?php echo $key; ?>"
										   class="remove-ip-exclusion"
										   href="#">
										   		<img class="x-remove-symbol" src="<?php echo APP_DESIGN_URL . '/img/remove.png'; ?>" title="Remove Exclusion" />
										</a>
									</li>
								<?php endforeach; ?>
							</ul>	
					<?php endforeach; ?>
				</div>
			</div>
		</div>
</div>