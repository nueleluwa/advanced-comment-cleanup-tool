<?php
/**
 * Plugin Name: Advanced Comment Cleanup Tool
 * Plugin URI: https://github.com/nueleluwa/Auto-Delete-Comments
 * Description: Automatically delete comments in batches with configurable scheduling. Modern UI with advanced analytics and REST API.
 * Version: 2.0.6
 * Author: Emmanuel Eluwa
 * Author URI: https://emmanueleluwa.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: advanced-comment-cleanup
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 *
 * @package Advanced_Comment_Cleanup
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class
 */
class Auto_Delete_Comments {

	/**
	 * Option name for settings
	 *
	 * @var string
	 */
	private $option_name = 'adc_settings';

	/**
	 * Cron hook name
	 *
	 * @var string
	 */
	private $cron_hook = 'adc_delete_comments_batch';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '2.0.6';

	/**
	 * Singleton instance
	 *
	 * @var Auto_Delete_Comments
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return Auto_Delete_Comments
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Register custom cron schedules early.
		add_filter( 'cron_schedules', array( $this, 'register_cron_schedules' ) );

		// Admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Register settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Register cron hook.
		add_action( $this->cron_hook, array( $this, 'delete_comments_batch' ) );

		// Cron reschedule hook.
		add_action( 'adc_reschedule_cron', array( $this, 'schedule_cron' ) );

		// Activation/Deactivation hooks.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_adc_delete_now', array( $this, 'ajax_delete_now' ) );
		add_action( 'wp_ajax_adc_get_stats', array( $this, 'ajax_get_stats' ) );
		add_action( 'wp_ajax_adc_get_analytics', array( $this, 'ajax_get_analytics' ) );

		// Admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Settings link on plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );

		// REST API endpoints.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// Hook to update options to trigger cron reschedule.
		add_action( 'update_option_' . $this->option_name, array( $this, 'on_settings_update' ), 10, 2 );
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Check for minimum requirements.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'This plugin requires PHP 7.4 or higher.', 'advanced-comment-cleanup' ) );
		}

		// Set default options.
		$default_options = array(
			'enabled'          => false,
			'batch_size'       => 15,
			'interval'         => 5,
			'delete_spam'      => true,
			'delete_pending'   => false,
			'delete_approved'  => false,
			'delete_trash'     => true,
			'older_than_days'  => 0,
		);

		if ( ! get_option( $this->option_name ) ) {
			add_option( $this->option_name, $default_options, '', 'no' );
		}

		// Initialize analytics data.
		if ( ! get_option( 'adc_analytics' ) ) {
			add_option( 'adc_analytics', array(), '', 'no' );
		}
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		$this->unschedule_cron();
	}

	/**
	 * Register REST API routes
	 */
	public function register_rest_routes() {
		register_rest_route(
			'advanced-comment-cleanup/v1',
			'/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_stats' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		register_rest_route(
			'advanced-comment-cleanup/v1',
			'/analytics',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'rest_get_analytics' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	/**
	 * REST API: Get stats
	 *
	 * @return WP_REST_Response
	 */
	public function rest_get_stats() {
		$comment_counts = wp_count_comments();

		return new WP_REST_Response(
			array(
				'spam'     => (int) $comment_counts->spam,
				'pending'  => (int) $comment_counts->moderated,
				'approved' => (int) $comment_counts->approved,
				'trash'    => (int) $comment_counts->trash,
				'total'    => (int) $comment_counts->total_comments,
			)
		);
	}

	/**
	 * REST API: Get analytics
	 *
	 * @return WP_REST_Response
	 */
	public function rest_get_analytics() {
		$analytics = $this->get_analytics_data();
		return new WP_REST_Response( $analytics );
	}

	/**
	 * Schedule cron job
	 */
	private function schedule_cron() {
		$options = get_option( $this->option_name );

		// Always clear existing schedule first.
		$this->unschedule_cron();

		// Validate options exist and are array.
		if ( ! is_array( $options ) || empty( $options['enabled'] ) ) {
			return;
		}

		// Get interval with validation.
		$interval = isset( $options['interval'] ) ? absint( $options['interval'] ) : 5;
		$interval = max( 1, min( 60, $interval ) );

		// Get the interval key and make sure it's registered.
		$interval_key = $this->get_cron_interval_key( $interval );

		// Make sure schedules are registered.
		$schedules = wp_get_schedules();
		if ( ! isset( $schedules[ $interval_key ] ) ) {
			// Manually register the schedule if it's not available yet.
			add_filter( 'cron_schedules', array( $this, 'register_cron_schedules' ) );
		}

		// Schedule the event - force it even if one exists.
		$scheduled = wp_schedule_event( time(), $interval_key, $this->cron_hook );
		
		// Debug: Log scheduling attempt.
		if ( false === $scheduled ) {
			error_log( 'Auto Delete Comments: Failed to schedule cron job. Interval: ' . $interval_key );
		} else {
			error_log( 'Auto Delete Comments: Successfully scheduled cron job. Next run: ' . wp_date( 'Y-m-d H:i:s', wp_next_scheduled( $this->cron_hook ) ) );
		}
	}

	/**
	 * Unschedule cron job
	 */
	private function unschedule_cron() {
		$timestamp = wp_next_scheduled( $this->cron_hook );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->cron_hook );
		}
		wp_clear_scheduled_hook( $this->cron_hook );
	}

	/**
	 * Register custom cron schedules
	 *
	 * @param array $schedules Existing schedules.
	 * @return array Modified schedules.
	 */
	public function register_cron_schedules( $schedules ) {
		// Register common intervals (1-60 minutes).
		for ( $i = 1; $i <= 60; $i++ ) {
			$key = 'adc_every_' . $i . '_minutes';
			if ( ! isset( $schedules[ $key ] ) ) {
				$schedules[ $key ] = array(
					'interval' => $i * MINUTE_IN_SECONDS,
					'display'  => sprintf(
						/* translators: %d: number of minutes */
						__( 'Every %d Minutes', 'advanced-comment-cleanup' ),
						$i
					),
				);
			}
		}
		return $schedules;
	}

	/**
	 * Get or create custom cron interval
	 *
	 * @param int $minutes Interval in minutes.
	 * @return string Interval key.
	 */
	private function get_cron_interval_key( $minutes ) {
		$minutes = absint( $minutes );
		$minutes = max( 1, min( 60, $minutes ) );
		return 'adc_every_' . $minutes . '_minutes';
	}

	/**
	 * Delete comments in batch
	 *
	 * @param bool $manual Whether this is a manual deletion.
	 * @return int Number of deleted comments.
	 */
	public function delete_comments_batch( $manual = false ) {
		$start_time = microtime( true );
		$options    = get_option( $this->option_name );

		if ( ! is_array( $options ) || empty( $options['enabled'] ) ) {
			// Allow manual deletion even when disabled.
			if ( ! $manual ) {
				return 0;
			}
		}

		$batch_size = isset( $options['batch_size'] ) ? absint( $options['batch_size'] ) : 15;
		$batch_size = max( 1, min( 50, $batch_size ) );

		$statuses = $this->get_comment_statuses( $options );
		
		// If no statuses selected, return early.
		if ( empty( $statuses ) ) {
			error_log( 'Auto Delete Comments: No comment types selected for deletion' );
			return 0;
		}

		$args = array(
			'number' => $batch_size,
			'status' => $statuses,
			'fields' => 'ids',
		);

		if ( ! empty( $options['older_than_days'] ) ) {
			$days               = absint( $options['older_than_days'] );
			$args['date_query'] = array(
				array(
					'before' => gmdate( 'Y-m-d H:i:s', strtotime( '-' . $days . ' days' ) ),
				),
			);
		}

		$comment_ids   = get_comments( $args );
		$deleted_count = 0;
		$deleted_by_type = array();

		if ( empty( $comment_ids ) ) {
			return 0;
		}

		// Track comment types being deleted.
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			if ( $comment ) {
				$status = $comment->comment_approved;
				if ( ! isset( $deleted_by_type[ $status ] ) ) {
					$deleted_by_type[ $status ] = 0;
				}
				
				if ( wp_delete_comment( $comment_id, true ) ) {
					++$deleted_count;
					++$deleted_by_type[ $status ];
				} else {
					error_log( 'Auto Delete Comments: Failed to delete comment ID ' . $comment_id );
				}
			}
		}

		if ( $deleted_count > 0 ) {
			$execution_time = round( ( microtime( true ) - $start_time ) * 1000, 2 ); // Convert to milliseconds.
			$this->log_deletion( $deleted_count, $deleted_by_type, $execution_time, $manual );
			$this->update_analytics( $deleted_count );
		}

		return $deleted_count;
	}

	/**
	 * Get comment statuses to delete
	 *
	 * @param array $options Plugin options.
	 * @return array Array of statuses.
	 */
	private function get_comment_statuses( $options ) {
		$statuses = array();

		if ( ! empty( $options['delete_spam'] ) ) {
			$statuses[] = 'spam';
		}
		if ( ! empty( $options['delete_pending'] ) ) {
			$statuses[] = 'hold';
		}
		if ( ! empty( $options['delete_approved'] ) ) {
			$statuses[] = 'approve';
		}
		if ( ! empty( $options['delete_trash'] ) ) {
			$statuses[] = 'trash';
		}

		return ! empty( $statuses ) ? $statuses : array();
	}

	/**
	 * Log deletion activity
	 *
	 * @param int   $count Number of deleted comments.
	 * @param array $deleted_by_type Comments deleted by type.
	 * @param float $execution_time Execution time in milliseconds.
	 * @param bool  $manual Whether this was a manual deletion.
	 */
	private function log_deletion( $count, $deleted_by_type = array(), $execution_time = 0, $manual = false ) {
		$log = get_option( 'adc_deletion_log', array() );

		if ( ! is_array( $log ) ) {
			$log = array();
		}

		// Convert status codes to readable types.
		$types = array();
		foreach ( $deleted_by_type as $status => $type_count ) {
			switch ( $status ) {
				case 'spam':
					$types['spam'] = $type_count;
					break;
				case '0':
					$types['pending'] = $type_count;
					break;
				case '1':
					$types['approved'] = $type_count;
					break;
				case 'trash':
					$types['trash'] = $type_count;
					break;
				default:
					$types[ $status ] = $type_count;
			}
		}

		$log[] = array(
			'date'           => current_time( 'mysql' ),
			'count'          => absint( $count ),
			'types'          => $types,
			'execution_time' => $execution_time,
			'method'         => $manual ? 'manual' : 'automatic',
			'user_id'        => $manual ? get_current_user_id() : 0,
		);

		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}

		update_option( 'adc_deletion_log', $log, 'no' );
	}

	/**
	 * Update analytics data
	 *
	 * @param int $count Number of deleted comments.
	 */
	private function update_analytics( $count ) {
		$analytics = get_option( 'adc_analytics', array() );

		if ( ! is_array( $analytics ) ) {
			$analytics = array();
		}

		$date = gmdate( 'Y-m-d' );

		if ( ! isset( $analytics[ $date ] ) ) {
			$analytics[ $date ] = 0;
		}

		$analytics[ $date ] += absint( $count );

		// Keep only last 30 days.
		if ( count( $analytics ) > 30 ) {
			$analytics = array_slice( $analytics, -30, null, true );
		}

		update_option( 'adc_analytics', $analytics, 'no' );
	}

	/**
	 * Get analytics data
	 *
	 * @return array Analytics data.
	 */
	private function get_analytics_data() {
		$analytics = get_option( 'adc_analytics', array() );
		$log       = get_option( 'adc_deletion_log', array() );

		$total_deleted = array_sum( array_column( $log, 'count' ) );
		$avg_per_run   = count( $log ) > 0 ? round( $total_deleted / count( $log ), 1 ) : 0;

		// Last 7 days data.
		$last_7_days = array();
		for ( $i = 6; $i >= 0; $i-- ) {
			$date                 = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
			$last_7_days[ $date ] = isset( $analytics[ $date ] ) ? $analytics[ $date ] : 0;
		}

		return array(
			'total_deleted' => $total_deleted,
			'avg_per_run'   => $avg_per_run,
			'total_runs'    => count( $log ),
			'last_7_days'   => $last_7_days,
		);
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Advanced Comment Cleanup', 'advanced-comment-cleanup' ),
			__( 'Advanced Comment Cleanup', 'advanced-comment-cleanup' ),
			'manage_options',
			'advanced-comment-cleanup',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			$this->option_name,
			$this->option_name,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input User input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['enabled']         = ! empty( $input['enabled'] );
		$sanitized['batch_size']      = isset( $input['batch_size'] ) ? max( 1, min( 50, absint( $input['batch_size'] ) ) ) : 15;
		$sanitized['interval']        = isset( $input['interval'] ) ? max( 1, min( 60, absint( $input['interval'] ) ) ) : 5;
		$sanitized['delete_spam']     = ! empty( $input['delete_spam'] );
		$sanitized['delete_pending']  = ! empty( $input['delete_pending'] );
		$sanitized['delete_approved'] = ! empty( $input['delete_approved'] );
		$sanitized['delete_trash']    = ! empty( $input['delete_trash'] );
		$sanitized['older_than_days'] = isset( $input['older_than_days'] ) ? absint( $input['older_than_days'] ) : 0;

		// Validate that at least one comment type is selected if enabled.
		if ( $sanitized['enabled'] ) {
			$has_type = $sanitized['delete_spam'] || $sanitized['delete_pending'] || 
						$sanitized['delete_approved'] || $sanitized['delete_trash'];
			
			if ( ! $has_type ) {
				$sanitized['enabled'] = false;
				add_settings_error(
					$this->option_name,
					'no_types_selected',
					__( 'Please select at least one comment type to delete. Auto-delete has been disabled.', 'advanced-comment-cleanup' ),
					'error'
				);
				return $sanitized;
			}
		}

		add_settings_error(
			$this->option_name,
			'settings_updated',
			__( 'Settings saved successfully.', 'advanced-comment-cleanup' ),
			'success'
		);

		return $sanitized;
	}

	/**
	 * Handle settings update
	 *
	 * @param array $old_value Old option value.
	 * @param array $new_value New option value.
	 */
	public function on_settings_update( $old_value, $new_value ) {
		// Reschedule cron when settings change.
		$this->schedule_cron();
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'settings_page_advanced-comment-cleanup' !== $hook ) {
			return;
		}

		// Chart.js for analytics.
		wp_enqueue_script(
			'chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
			array(),
			'4.4.0',
			true
		);

		// Modern admin CSS
		wp_enqueue_style(
			'acc-admin-modern',
			plugins_url( 'assets/admin-modern.css', __FILE__ ),
			array(),
			$this->version
		);

		// Modern admin JS
		wp_enqueue_script(
			'acc-admin-modern',
			plugins_url( 'assets/admin-modern.js', __FILE__ ),
			array( 'jquery', 'chartjs' ),
			$this->version,
			true
		);

		// Localize script with translations and data
		wp_localize_script(
			'acc-admin-modern',
			'accData',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'rest_url' => rest_url( 'advanced-comment-cleanup/v1' ),
				'nonce'    => wp_create_nonce( 'adc_nonce' ),
				'i18n'     => array(
					'confirmDelete'    => __( 'Are you sure you want to delete a batch of comments now? This action cannot be undone.', 'advanced-comment-cleanup' ),
					'processing'       => __( 'Processing...', 'advanced-comment-cleanup' ),
					'deleteNow'        => __( 'Delete Now', 'advanced-comment-cleanup' ),
					'refreshing'       => __( 'Refreshing...', 'advanced-comment-cleanup' ),
					'refreshStats'     => __( 'Refresh Stats', 'advanced-comment-cleanup' ),
					'error'            => __( 'Error', 'advanced-comment-cleanup' ),
					'ajaxError'        => __( 'AJAX Error', 'advanced-comment-cleanup' ),
					'batchSizeLimit'   => __( 'Batch size limited to 50 to prevent server errors.', 'advanced-comment-cleanup' ),
					'approvedWarning'  => __( 'WARNING: Enabling this option will delete approved comments. This action cannot be undone. Are you sure?', 'advanced-comment-cleanup' ),
					'commentsDeleted'  => __( 'Comments Deleted', 'advanced-comment-cleanup' ),
						'success'            => __( 'Success', 'advanced-comment-cleanup' ),
						'statsRefreshed'     => __( 'Statistics refreshed successfully', 'advanced-comment-cleanup' ),
						'statsRefreshError'  => __( 'Failed to refresh statistics', 'advanced-comment-cleanup' ),
				),
			)
		);
	}

	/**
	 * AJAX: Delete now
	 */
	public function ajax_delete_now() {
		check_ajax_referer( 'adc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'advanced-comment-cleanup' ) ) );
		}

		$deleted = $this->delete_comments_batch( true );

		wp_send_json_success(
			array(
				'deleted' => $deleted,
				'message' => sprintf(
					/* translators: %d: number of deleted comments */
					__( 'Deleted %d comments', 'advanced-comment-cleanup' ),
					$deleted
				),
			)
		);
	}

	/**
	 * AJAX: Get stats
	 */
	public function ajax_get_stats() {
		check_ajax_referer( 'adc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'advanced-comment-cleanup' ) ) );
		}

		$comment_counts = wp_count_comments();

		$stats = array(
			'spam'     => (int) $comment_counts->spam,
			'pending'  => (int) $comment_counts->moderated,
			'approved' => (int) $comment_counts->approved,
			'trash'    => (int) $comment_counts->trash,
			'total'    => (int) $comment_counts->total_comments,
		);

		wp_send_json_success( $stats );
	}

	/**
	 * AJAX: Get analytics
	 */
	public function ajax_get_analytics() {
		check_ajax_referer( 'adc_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized access.', 'advanced-comment-cleanup' ) ) );
		}

		$analytics = $this->get_analytics_data();
		wp_send_json_success( $analytics );
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'advanced-comment-cleanup' ) );
		}

		$options = wp_parse_args(
			get_option( $this->option_name, array() ),
			array(
				'enabled'          => false,
				'batch_size'       => 15,
				'interval'         => 5,
				'delete_spam'      => true,
				'delete_pending'   => false,
				'delete_approved'  => false,
				'delete_trash'     => true,
				'older_than_days'  => 0,
			)
		);

		$log            = get_option( 'adc_deletion_log', array() );
		$comment_counts = wp_count_comments();
		$analytics      = $this->get_analytics_data();
		$option_name    = $this->option_name;
		$cron_hook      = $this->cron_hook;

		// Load modern admin template
		include plugin_dir_path( __FILE__ ) . 'views/admin-page-modern.php';
	}

	/**
	 * Add action links to plugin list
	 *
	 * @param array $links Existing links.
	 * @return array Modified links.
	 */
	public function add_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=advanced-comment-cleanup' ) ),
			__( 'Settings', 'advanced-comment-cleanup' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}

/**
 * Initialize plugin
 */
function adc_init() {
	return Auto_Delete_Comments::get_instance();
}
add_action( 'plugins_loaded', 'adc_init' );
