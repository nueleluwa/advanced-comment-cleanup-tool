<?php
/**
 * Modern Admin Interface Template
 *
 * @package Advanced_Comment_Cleanup
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="acc-wrap">
	<!-- Header Bar -->
	<div class="acc-header">
		<div class="acc-header-left">
			<div class="acc-logo">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M19 9l-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</div>
			<div>
				<h1 class="acc-header-title"><?php echo esc_html__( 'Advanced Comment Cleanup', 'advanced-comment-cleanup' ); ?></h1>
				<p class="acc-header-subtitle"><?php echo esc_html__( 'Automated comment management for WordPress', 'advanced-comment-cleanup' ); ?></p>
			</div>
		</div>
		<div class="acc-header-actions">
			<button type="button" class="acc-btn acc-btn-secondary" id="acc-refresh-stats">
				<svg class="acc-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
				</svg>
				<?php echo esc_html__( 'Refresh Stats', 'advanced-comment-cleanup' ); ?>
			</button>
			<button type="button" class="acc-btn acc-btn-primary" id="acc-delete-now">
				<svg class="acc-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
				</svg>
				<?php echo esc_html__( 'Delete Now', 'advanced-comment-cleanup' ); ?>
			</button>
		</div>
	</div>

	<!-- Main Content Area -->
	<div class="acc-content">
		
		<!-- Statistics Grid -->
		<div class="acc-stats-grid">
			<div class="acc-stat-card">
				<span class="acc-stat-label"><?php echo esc_html__( 'Spam Comments', 'advanced-comment-cleanup' ); ?></span>
				<span class="acc-stat-value spam-count"><?php echo esc_html( number_format_i18n( $comment_counts->spam ) ); ?></span>
				<div class="acc-stat-change acc-positive">
					<svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
						<path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
					</svg>
					<?php echo esc_html__( 'Ready for cleanup', 'advanced-comment-cleanup' ); ?>
				</div>
			</div>

			<div class="acc-stat-card">
				<span class="acc-stat-label"><?php echo esc_html__( 'Pending Review', 'advanced-comment-cleanup' ); ?></span>
				<span class="acc-stat-value pending-count"><?php echo esc_html( number_format_i18n( $comment_counts->moderated ) ); ?></span>
			</div>

			<div class="acc-stat-card">
				<span class="acc-stat-label"><?php echo esc_html__( 'Approved', 'advanced-comment-cleanup' ); ?></span>
				<span class="acc-stat-value approved-count"><?php echo esc_html( number_format_i18n( $comment_counts->approved ) ); ?></span>
			</div>

			<div class="acc-stat-card">
				<span class="acc-stat-label"><?php echo esc_html__( 'Trash', 'advanced-comment-cleanup' ); ?></span>
				<span class="acc-stat-value trash-count"><?php echo esc_html( number_format_i18n( $comment_counts->trash ) ); ?></span>
			</div>

			<div class="acc-stat-card">
				<span class="acc-stat-label"><?php echo esc_html__( 'Total Comments', 'advanced-comment-cleanup' ); ?></span>
				<span class="acc-stat-value total-count"><?php echo esc_html( number_format_i18n( $comment_counts->total_comments ) ); ?></span>
			</div>
		</div>

		<!-- Tabs Navigation -->
		<div class="acc-tabs">
			<ul class="acc-tabs-nav">
				<li><a href="#acc-tab-dashboard" class="acc-tab acc-active" data-tab="dashboard">
					<?php echo esc_html__( 'Dashboard', 'advanced-comment-cleanup' ); ?>
				</a></li>
				<li><a href="#acc-tab-settings" class="acc-tab" data-tab="settings">
					<?php echo esc_html__( 'Settings', 'advanced-comment-cleanup' ); ?>
				</a></li>
				<li><a href="#acc-tab-history" class="acc-tab" data-tab="history">
					<?php echo esc_html__( 'History', 'advanced-comment-cleanup' ); ?>
				</a></li>
				<li><a href="#acc-tab-analytics" class="acc-tab" data-tab="analytics">
					<?php echo esc_html__( 'Analytics', 'advanced-comment-cleanup' ); ?>
				</a></li>
			</ul>

			<!-- Dashboard Tab -->
			<div id="acc-tab-dashboard" class="acc-tab-content acc-active">
				<div class="acc-tab-pane">
					
					<!-- Status Card -->
					<div class="acc-card">
						<div class="acc-card-header">
							<h2 class="acc-card-title"><?php echo esc_html__( 'System Status', 'advanced-comment-cleanup' ); ?></h2>
							<span class="acc-badge <?php echo $options['enabled'] ? 'acc-badge-success' : 'acc-badge-gray'; ?>">
								<?php echo $options['enabled'] ? esc_html__( 'Active', 'advanced-comment-cleanup' ) : esc_html__( 'Inactive', 'advanced-comment-cleanup' ); ?>
							</span>
						</div>
						<div class="acc-card-body">
							<table class="acc-form-table">
								<tr>
									<th><?php echo esc_html__( 'Current Status', 'advanced-comment-cleanup' ); ?></th>
									<td>
										<?php if ( $options['enabled'] ) : ?>
											<span class="acc-badge acc-badge-success">
												<svg width="12" height="12" fill="currentColor" viewBox="0 0 20 20">
													<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
												</svg>
												<?php echo esc_html__( 'Automatic deletion is active', 'advanced-comment-cleanup' ); ?>
											</span>
										<?php else : ?>
											<span class="acc-badge acc-badge-gray">
												<?php echo esc_html__( 'Automatic deletion is disabled', 'advanced-comment-cleanup' ); ?>
											</span>
										<?php endif; ?>
									</td>
								</tr>
								<?php if ( $options['enabled'] ) : ?>
								<tr>
									<th><?php echo esc_html__( 'Next Scheduled Run', 'advanced-comment-cleanup' ); ?></th>
									<td>
										<?php
										$next_run = wp_next_scheduled( $cron_hook );
										if ( $next_run ) {
											echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next_run ) );
										} else {
											echo esc_html__( 'Not scheduled', 'advanced-comment-cleanup' );
										}
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo esc_html__( 'Configuration', 'advanced-comment-cleanup' ); ?></th>
									<td>
										<?php
										echo esc_html(
											sprintf(
												/* translators: 1: batch size, 2: interval in minutes */
												__( '%1$d comments every %2$d minutes', 'advanced-comment-cleanup' ),
												$options['batch_size'],
												$options['interval']
											)
										);
										?>
									</td>
								</tr>
								<tr>
									<th><?php echo esc_html__( 'Target Types', 'advanced-comment-cleanup' ); ?></th>
									<td>
										<?php
										$types = array();
										if ( ! empty( $options['delete_spam'] ) ) {
											$types[] = '<span class="acc-badge acc-badge-warning">' . esc_html__( 'Spam', 'advanced-comment-cleanup' ) . '</span>';
										}
										if ( ! empty( $options['delete_pending'] ) ) {
											$types[] = '<span class="acc-badge acc-badge-info">' . esc_html__( 'Pending', 'advanced-comment-cleanup' ) . '</span>';
										}
										if ( ! empty( $options['delete_trash'] ) ) {
											$types[] = '<span class="acc-badge acc-badge-gray">' . esc_html__( 'Trash', 'advanced-comment-cleanup' ) . '</span>';
										}
										if ( ! empty( $options['delete_approved'] ) ) {
											$types[] = '<span class="acc-badge acc-badge-error">' . esc_html__( 'Approved', 'advanced-comment-cleanup' ) . '</span>';
										}
										echo implode( ' ', $types );
										?>
									</td>
								</tr>
								<?php endif; ?>
							</table>
						</div>
					</div>

					<!-- Quick Actions -->
					<div class="acc-card">
						<div class="acc-card-header">
							<h2 class="acc-card-title"><?php echo esc_html__( 'Quick Actions', 'advanced-comment-cleanup' ); ?></h2>
						</div>
						<div class="acc-card-body">
							<div class="acc-alert acc-alert-info">
								<svg class="acc-alert-icon" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
								</svg>
								<div class="acc-alert-content">
									<p class="acc-alert-message">
										<?php echo esc_html__( 'Delete a batch of comments immediately using your current settings. This action cannot be undone.', 'advanced-comment-cleanup' ); ?>
									</p>
								</div>
							</div>
							
							<div id="acc-delete-result" style="margin-top: 16px;"></div>
						</div>
					</div>

					<!-- Recent Activity -->
					<?php if ( ! empty( $analytics['total_deleted'] ) ) : ?>
					<div class="acc-card">
						<div class="acc-card-header">
							<h2 class="acc-card-title"><?php echo esc_html__( 'Performance Summary', 'advanced-comment-cleanup' ); ?></h2>
						</div>
						<div class="acc-card-body">
							<div class="acc-stats-grid">
								<div class="acc-stat-card">
									<span class="acc-stat-label"><?php echo esc_html__( 'Total Deleted', 'advanced-comment-cleanup' ); ?></span>
									<span class="acc-stat-value"><?php echo esc_html( number_format_i18n( $analytics['total_deleted'] ) ); ?></span>
								</div>
								<div class="acc-stat-card">
									<span class="acc-stat-label"><?php echo esc_html__( 'Average Per Run', 'advanced-comment-cleanup' ); ?></span>
									<span class="acc-stat-value"><?php echo esc_html( number_format_i18n( $analytics['avg_per_run'], 1 ) ); ?></span>
								</div>
								<div class="acc-stat-card">
									<span class="acc-stat-label"><?php echo esc_html__( 'Total Runs', 'advanced-comment-cleanup' ); ?></span>
									<span class="acc-stat-value"><?php echo esc_html( number_format_i18n( $analytics['total_runs'] ) ); ?></span>
								</div>
							</div>
						</div>
					</div>
					<?php endif; ?>

				</div>
			</div>

			<!-- Settings Tab -->
			<div id="acc-tab-settings" class="acc-tab-content">
				<div class="acc-tab-pane">
					<form method="post" action="options.php">
						<?php settings_fields( $option_name ); ?>
						
						<div class="acc-card">
							<div class="acc-card-header">
								<h2 class="acc-card-title"><?php echo esc_html__( 'General Settings', 'advanced-comment-cleanup' ); ?></h2>
							</div>
							<div class="acc-card-body">
								<table class="acc-form-table">
									<tr>
										<th><?php echo esc_html__( 'Enable Auto Delete', 'advanced-comment-cleanup' ); ?></th>
										<td>
											<label class="acc-toggle">
												<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[enabled]" value="1" <?php checked( $options['enabled'], true ); ?>>
												<span class="acc-toggle-switch"></span>
												<span class="acc-toggle-label"><?php echo esc_html__( 'Automatically delete comments on schedule', 'advanced-comment-cleanup' ); ?></span>
											</label>
											<p class="acc-form-help">
												<?php echo esc_html__( 'Enable or disable automatic comment deletion. Comments will be processed in batches according to your schedule.', 'advanced-comment-cleanup' ); ?>
											</p>
										</td>
									</tr>
									<tr>
										<th><?php echo esc_html__( 'Batch Size', 'advanced-comment-cleanup' ); ?></th>
										<td>
											<input type="number" name="<?php echo esc_attr( $option_name ); ?>[batch_size]" value="<?php echo esc_attr( $options['batch_size'] ); ?>" min="1" max="50" class="acc-form-control" style="width: 100px;">
											<p class="acc-form-help">
												<?php echo esc_html__( 'Number of comments to delete per batch (1-50). Limited to 50 to prevent server errors.', 'advanced-comment-cleanup' ); ?>
											</p>
										</td>
									</tr>
									<tr>
										<th><?php echo esc_html__( 'Interval', 'advanced-comment-cleanup' ); ?></th>
										<td>
											<input type="number" name="<?php echo esc_attr( $option_name ); ?>[interval]" value="<?php echo esc_attr( $options['interval'] ); ?>" min="1" max="60" class="acc-form-control" style="width: 100px;">
											<span class="acc-text-muted"><?php echo esc_html__( 'minutes', 'advanced-comment-cleanup' ); ?></span>
											<p class="acc-form-help">
												<?php echo esc_html__( 'How often to run the deletion batch (1-60 minutes)', 'advanced-comment-cleanup' ); ?>
											</p>
										</td>
									</tr>
								</table>
							</div>
						</div>

						<div class="acc-card">
							<div class="acc-card-header">
								<h2 class="acc-card-title"><?php echo esc_html__( 'Comment Types', 'advanced-comment-cleanup' ); ?></h2>
							</div>
							<div class="acc-card-body">
								<table class="acc-form-table">
									<tr>
										<th><?php echo esc_html__( 'Target Comment Types', 'advanced-comment-cleanup' ); ?></th>
										<td>
											<div style="display: flex; flex-direction: column; gap: 12px;">
												<label class="acc-checkbox">
													<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[delete_spam]" value="1" <?php checked( $options['delete_spam'], true ); ?>>
													<span class="acc-checkbox-box"></span>
													<span class="acc-checkbox-label">
														<?php echo esc_html__( 'Spam Comments', 'advanced-comment-cleanup' ); ?>
														<span class="acc-badge acc-badge-success acc-ml-sm"><?php echo esc_html__( 'Recommended', 'advanced-comment-cleanup' ); ?></span>
													</span>
												</label>
												
												<label class="acc-checkbox">
													<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[delete_pending]" value="1" <?php checked( $options['delete_pending'], true ); ?>>
													<span class="acc-checkbox-box"></span>
													<span class="acc-checkbox-label"><?php echo esc_html__( 'Pending Comments', 'advanced-comment-cleanup' ); ?></span>
												</label>
												
												<label class="acc-checkbox">
													<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[delete_trash]" value="1" <?php checked( $options['delete_trash'], true ); ?>>
													<span class="acc-checkbox-box"></span>
													<span class="acc-checkbox-label"><?php echo esc_html__( 'Trash Comments', 'advanced-comment-cleanup' ); ?></span>
												</label>
												
												<label class="acc-checkbox">
													<input type="checkbox" name="<?php echo esc_attr( $option_name ); ?>[delete_approved]" value="1" <?php checked( $options['delete_approved'], true ); ?>>
													<span class="acc-checkbox-box"></span>
													<span class="acc-checkbox-label">
														<?php echo esc_html__( 'Approved Comments', 'advanced-comment-cleanup' ); ?>
														<span class="acc-badge acc-badge-error acc-ml-sm"><?php echo esc_html__( 'Use with Caution', 'advanced-comment-cleanup' ); ?></span>
													</span>
												</label>
											</div>
											<p class="acc-form-help">
												<?php echo esc_html__( 'Select which types of comments to automatically delete. Be careful with approved comments as they are legitimate user contributions.', 'advanced-comment-cleanup' ); ?>
											</p>
										</td>
									</tr>
									<tr>
										<th><?php echo esc_html__( 'Age Filter', 'advanced-comment-cleanup' ); ?></th>
										<td>
											<input type="number" name="<?php echo esc_attr( $option_name ); ?>[older_than_days]" value="<?php echo esc_attr( $options['older_than_days'] ); ?>" min="0" class="acc-form-control" style="width: 100px;">
											<span class="acc-text-muted"><?php echo esc_html__( 'days', 'advanced-comment-cleanup' ); ?></span>
											<p class="acc-form-help">
												<?php echo esc_html__( 'Only delete comments older than this many days. Set to 0 to delete all matching comments regardless of age.', 'advanced-comment-cleanup' ); ?>
											</p>
										</td>
									</tr>
								</table>
							</div>
							<div class="acc-card-footer">
								<?php submit_button( __( 'Save Settings', 'advanced-comment-cleanup' ), 'acc-btn acc-btn-primary', 'submit', false ); ?>
							</div>
						</div>
					</form>
				</div>
			</div>

			<!-- History Tab -->
			<div id="acc-tab-history" class="acc-tab-content">
				<div class="acc-tab-pane">
					<div class="acc-card">
						<div class="acc-card-header">
							<h2 class="acc-card-title"><?php echo esc_html__( 'Deletion History', 'advanced-comment-cleanup' ); ?></h2>
							<p class="acc-text-muted"><?php echo esc_html__( 'Last 20 deletion events', 'advanced-comment-cleanup' ); ?></p>
						</div>
						<div class="acc-card-body">
							<?php if ( ! empty( $log ) && is_array( $log ) ) : ?>
							<table class="acc-table">
								<thead>
									<tr>
										<th><?php echo esc_html__( 'Date & Time', 'advanced-comment-cleanup' ); ?></th>
										<th><?php echo esc_html__( 'Comments Deleted', 'advanced-comment-cleanup' ); ?></th>
										<th><?php echo esc_html__( 'Types', 'advanced-comment-cleanup' ); ?></th>
										<th><?php echo esc_html__( 'Method', 'advanced-comment-cleanup' ); ?></th>
										<th><?php echo esc_html__( 'Execution Time', 'advanced-comment-cleanup' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( array_reverse( array_slice( $log, -20 ) ) as $entry ) : ?>
									<tr>
										<td>
											<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry['date'] ) ) ); ?>
										</td>
										<td><strong><?php echo esc_html( number_format_i18n( $entry['count'] ) ); ?></strong></td>
										<td>
											<?php
											if ( ! empty( $entry['types'] ) && is_array( $entry['types'] ) ) :
												$type_badges = array();
												foreach ( $entry['types'] as $type => $count ) :
													$badge_class = 'acc-badge-gray';
													$type_label  = ucfirst( $type );
													
													if ( 'spam' === $type ) {
														$badge_class = 'acc-badge-warning';
														$type_label  = __( 'Spam', 'advanced-comment-cleanup' );
													} elseif ( 'pending' === $type ) {
														$badge_class = 'acc-badge-info';
														$type_label  = __( 'Pending', 'advanced-comment-cleanup' );
													} elseif ( 'approved' === $type ) {
														$badge_class = 'acc-badge-success';
														$type_label  = __( 'Approved', 'advanced-comment-cleanup' );
													} elseif ( 'trash' === $type ) {
														$badge_class = 'acc-badge-error';
														$type_label  = __( 'Trash', 'advanced-comment-cleanup' );
													}
													
													$type_badges[] = sprintf(
														'<span class="acc-badge %s" style="font-size: 11px;">%s: %d</span>',
														esc_attr( $badge_class ),
														esc_html( $type_label ),
														absint( $count )
													);
												endforeach;
												echo implode( ' ', $type_badges ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											else :
												echo '<span class="acc-text-muted">—</span>';
											endif;
											?>
										</td>
										<td>
											<?php
											$method = isset( $entry['method'] ) ? $entry['method'] : 'automatic';
											if ( 'manual' === $method ) {
												echo '<span class="acc-badge acc-badge-primary" style="font-size: 11px;">' . esc_html__( 'Manual', 'advanced-comment-cleanup' ) . '</span>';
											} else {
												echo '<span class="acc-badge acc-badge-gray" style="font-size: 11px;">' . esc_html__( 'Automatic', 'advanced-comment-cleanup' ) . '</span>';
											}
											?>
										</td>
										<td>
											<?php
											if ( isset( $entry['execution_time'] ) && $entry['execution_time'] > 0 ) {
												echo '<span class="acc-text-muted">' . esc_html( number_format( $entry['execution_time'], 2 ) ) . ' ms</span>';
											} else {
												echo '<span class="acc-text-muted">—</span>';
											}
											?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<?php else : ?>
							<div class="acc-alert acc-alert-info">
								<svg class="acc-alert-icon" fill="currentColor" viewBox="0 0 20 20">
									<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
								</svg>
								<div class="acc-alert-content">
									<p class="acc-alert-message"><?php echo esc_html__( 'No deletion history yet. Enable automatic deletion to start tracking activity.', 'advanced-comment-cleanup' ); ?></p>
								</div>
							</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<!-- Analytics Tab -->
			<div id="acc-tab-analytics" class="acc-tab-content">
				<div class="acc-tab-pane">
					<div class="acc-card">
						<div class="acc-card-header">
							<h2 class="acc-card-title"><?php echo esc_html__( '7-Day Analytics', 'advanced-comment-cleanup' ); ?></h2>
						</div>
						<div class="acc-card-body">
							<canvas id="acc-analytics-chart" height="80"></canvas>
						</div>
					</div>
				</div>
			</div>

		</div><!-- .acc-tabs -->

	</div><!-- .acc-content -->
</div><!-- .acc-wrap -->
