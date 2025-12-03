/**
 * Advanced Comment Cleanup Tool - Modern Admin Interface JavaScript
 * 
 * @package Advanced_Comment_Cleanup
 * @version 2.0.2
 */

(function($) {
	'use strict';

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		ACC.init();
	});

	/**
	 * Main Application Object
	 */
	const ACC = {
		
		/**
		 * Initialize all components
		 */
		init: function() {
			this.initToastContainer();
			this.tabs();
			this.deleteNow();
			this.refreshStats();
			this.autoRefresh();
			this.formValidation();
			this.warningMessages();
			this.analytics();
			this.handleSettingsSaved();
		},

		/**
		 * Initialize toast notification container
		 */
		initToastContainer: function() {
			if (!$('#acc-toast-container').length) {
				$('body').append('<div id="acc-toast-container" class="acc-toast-container"></div>');
			}
		},

		/**
		 * Show toast notification
		 * @param {string} message - The message to display
		 * @param {string} type - Type of toast (success, error, warning, info)
		 * @param {string} title - Optional title for the toast
		 * @param {number} duration - Duration in milliseconds (0 for persistent)
		 */
		showToast: function(message, type = 'success', title = '', duration = 5000) {
			type = type || 'success';
			const toastId = 'acc-toast-' + Date.now();
			const container = $('#acc-toast-container');
			
			// Icon SVG based on type
			const icons = {
				success: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>',
				error: '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>',
				warning: '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>',
				info: '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>'
			};
			
			const titleHtml = title ? '<div class="acc-toast-title">' + this.escapeHtml(title) + '</div>' : '';
			
			const toastHtml = 
				'<div id="' + toastId + '" class="acc-toast acc-toast-' + type + '">' +
					'<div class="acc-toast-icon">' +
						'<svg fill="currentColor" viewBox="0 0 20 20">' + icons[type] + '</svg>' +
					'</div>' +
					'<div class="acc-toast-content">' +
						titleHtml +
						'<div class="acc-toast-message">' + this.escapeHtml(message) + '</div>' +
					'</div>' +
					'<button type="button" class="acc-toast-close" data-toast-id="' + toastId + '">' +
						'<svg fill="currentColor" viewBox="0 0 20 20">' +
							'<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>' +
						'</svg>' +
					'</button>' +
				'</div>';
			
			container.append(toastHtml);
			
			// Close button handler
			$('#' + toastId).find('.acc-toast-close').on('click', function() {
				ACC.removeToast($(this).data('toast-id'));
			});
			
			// Auto-remove after duration if specified
			if (duration > 0) {
				setTimeout(function() {
					ACC.removeToast(toastId);
				}, duration);
			}
		},

		/**
		 * Remove toast notification
		 * @param {string} toastId - The ID of the toast to remove
		 */
		removeToast: function(toastId) {
			const toast = $('#' + toastId);
			if (toast.length) {
				toast.addClass('acc-toast-removing');
				setTimeout(function() {
					toast.remove();
				}, 300);
			}
		},

		/**
		 * Escape HTML to prevent XSS
		 * @param {string} text - Text to escape
		 * @return {string} Escaped text
		 */
		escapeHtml: function(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		},

		/**
		 * Tab Switching Functionality
		 */
		tabs: function() {
			$('.acc-tab').on('click', function(e) {
				e.preventDefault();
				
				const tabId = $(this).data('tab');
				const targetContent = $('#acc-tab-' + tabId);
				
				// Remove active class from all tabs and content
				$('.acc-tab').removeClass('acc-active');
				$('.acc-tab-content').removeClass('acc-active');
				
				// Add active class to clicked tab and corresponding content
				$(this).addClass('acc-active');
				targetContent.addClass('acc-active');
				
				// Update URL hash without scrolling
				if (history.pushState) {
					history.pushState(null, null, '#acc-tab-' + tabId);
				}
				
				// Trigger analytics chart update if switching to analytics tab
				if (tabId === 'analytics') {
					ACC.updateAnalyticsChart();
				}
			});
			
			// Handle direct URL hash on page load
			const hash = window.location.hash;
			if (hash && hash.startsWith('#acc-tab-')) {
				const tabName = hash.replace('#acc-tab-', '');
				$('.acc-tab[data-tab="' + tabName + '"]').trigger('click');
			}
		},

		/**
		 * Delete Batch Now Functionality
		 */
		deleteNow: function() {
			$('#acc-delete-now').on('click', function(e) {
				e.preventDefault();
				
				const button = $(this);
				
				// Confirm action
				if (!confirm(accData.i18n.confirmDelete)) {
					return;
				}
				
				// Disable button and show loading state
				button.prop('disabled', true).html(
					'<span class="acc-spinner"></span> ' + accData.i18n.processing
				);
				
				// AJAX request
				$.ajax({
					url: accData.ajax_url,
					type: 'POST',
					data: {
						action: 'adc_delete_now',
						nonce: accData.nonce
					},
					success: function(response) {
						if (response.success) {
							ACC.showToast(
								response.data.message,
								'success',
								accData.i18n.success || 'Success'
							);
							
							// Refresh statistics after successful deletion
							setTimeout(function() {
								ACC.updateStats();
							}, 500);
						} else {
							ACC.showToast(
								response.data.message || accData.i18n.error,
								'error',
								accData.i18n.error || 'Error'
							);
						}
					},
					error: function(xhr, status, error) {
						ACC.showToast(
							accData.i18n.ajaxError + ': ' + error,
							'error',
							accData.i18n.error || 'Error'
						);
					},
					complete: function() {
						// Reset button
						button.prop('disabled', false).html(
							'<svg class="acc-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
								'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
							'</svg>' +
							accData.i18n.deleteNow
						);
					}
				});
			});
		},

		/**
		 * Refresh Statistics Button
		 */
		refreshStats: function() {
			$('#acc-refresh-stats').on('click', function(e) {
				e.preventDefault();
				ACC.updateStats();
			});
		},

		/**
		 * Update Statistics via AJAX
		 */
		updateStats: function() {
			const button = $('#acc-refresh-stats');
			
			// Disable button and show loading
			button.prop('disabled', true).html(
				'<span class="acc-spinner"></span> ' + accData.i18n.refreshing
			);
			
			$.ajax({
				url: accData.ajax_url,
				type: 'POST',
				data: {
					action: 'adc_get_stats',
					nonce: accData.nonce
				},
				success: function(response) {
					if (response.success) {
						const stats = response.data;
						
						// Update stat values with animation
						ACC.animateStatValue('.spam-count', stats.spam);
						ACC.animateStatValue('.pending-count', stats.pending);
						ACC.animateStatValue('.approved-count', stats.approved);
						ACC.animateStatValue('.trash-count', stats.trash);
						ACC.animateStatValue('.total-count', stats.total);
						
						// Show success toast
						ACC.showToast(
							accData.i18n.statsRefreshed || 'Statistics refreshed successfully',
							'success',
							'',
							3000
						);
					} else {
						ACC.showToast(
							accData.i18n.statsRefreshError || 'Failed to refresh statistics',
							'error'
						);
					}
				},
				error: function(xhr, status, error) {
					console.error('Error refreshing stats:', error);
					ACC.showToast(
						accData.i18n.statsRefreshError || 'Failed to refresh statistics',
						'error'
					);
				},
				complete: function() {
					// Reset button
					button.prop('disabled', false).html(
						'<svg class="acc-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
							'<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>' +
						'</svg>' +
						accData.i18n.refreshStats
					);
				}
			});
		},

		/**
		 * Animate stat value changes
		 */
		animateStatValue: function(selector, newValue) {
			const element = $(selector);
			const currentValue = parseInt(element.text().replace(/,/g, '')) || 0;
			
			if (currentValue !== newValue) {
				element.css('opacity', '0.3');
				
				setTimeout(function() {
					element.text(newValue.toLocaleString());
					element.css('opacity', '1');
				}, 200);
			}
		},

		/**
		 * Auto-refresh statistics every 30 seconds when enabled
		 */
		autoRefresh: function() {
			let autoRefreshInterval;
			
			const startAutoRefresh = function() {
				const enabled = $('input[name$="[enabled]"]').is(':checked');
				
				if (enabled && !autoRefreshInterval) {
					autoRefreshInterval = setInterval(function() {
						ACC.updateStats();
					}, 30000); // 30 seconds
				}
			};
			
			const stopAutoRefresh = function() {
				if (autoRefreshInterval) {
					clearInterval(autoRefreshInterval);
					autoRefreshInterval = null;
				}
			};
			
			// Start auto-refresh if enabled on page load
			startAutoRefresh();
			
			// Update auto-refresh when toggle changes
			$('input[name$="[enabled]"]').on('change', function() {
				stopAutoRefresh();
				if ($(this).is(':checked')) {
					startAutoRefresh();
				}
			});
		},

		/**
		 * Form Validation
		 */
		formValidation: function() {
			// Batch size validation
			$('input[name$="[batch_size]"]').on('change', function() {
				let value = parseInt($(this).val());
				if (value < 1) {
					$(this).val(1);
				} else if (value > 50) {
					$(this).val(50);
					alert(accData.i18n.batchSizeLimit);
				}
			});
			
			// Interval validation
			$('input[name$="[interval]"]').on('change', function() {
				let value = parseInt($(this).val());
				if (value < 1) {
					$(this).val(1);
				} else if (value > 60) {
					$(this).val(60);
				}
			});
			
			// Age filter validation
			$('input[name$="[older_than_days]"]').on('change', function() {
				let value = parseInt($(this).val());
				if (value < 0) {
					$(this).val(0);
				}
			});
		},

		/**
		 * Warning messages for dangerous options
		 */
		warningMessages: function() {
			// Warning for approved comments
			$('input[name$="[delete_approved]"]').on('change', function() {
				if ($(this).is(':checked')) {
					if (!confirm(accData.i18n.approvedWarning)) {
						$(this).prop('checked', false);
					}
				}
			});
		},

		/**
		 * Handle WordPress settings saved notices
		 */
		handleSettingsSaved: function() {
			// Check for WordPress admin notices and convert to toast
			const $notices = $('.notice.settings-error');
			
			if ($notices.length > 0) {
				$notices.each(function() {
					const $notice = $(this);
					const message = $notice.find('p').text();
					let type = 'info';
					
					if ($notice.hasClass('notice-success') || $notice.hasClass('updated')) {
						type = 'success';
					} else if ($notice.hasClass('notice-error') || $notice.hasClass('error')) {
						type = 'error';
					} else if ($notice.hasClass('notice-warning')) {
						type = 'warning';
					}
					
					// Show toast
					setTimeout(function() {
						ACC.showToast(message, type, '', 5000);
					}, 100);
					
					// Hide the original notice
					$notice.hide();
				});
			}
		},

		/**
		 * Analytics Chart Initialization
		 */
		analytics: function() {
			// Chart will be initialized when analytics tab is first opened
			this.chartInstance = null;
		},

		/**
		 * Update Analytics Chart
		 */
		updateAnalyticsChart: function() {
			const canvas = document.getElementById('acc-analytics-chart');
			
			if (!canvas) {
				return;
			}
			
			// If chart already exists, return
			if (this.chartInstance) {
				return;
			}
			
			// Fetch analytics data
			$.ajax({
				url: accData.ajax_url,
				type: 'POST',
				data: {
					action: 'adc_get_analytics',
					nonce: accData.nonce
				},
				success: function(response) {
					if (response.success && response.data.last_7_days) {
						ACC.createChart(canvas, response.data.last_7_days);
					}
				},
				error: function(xhr, status, error) {
					console.error('Error loading analytics:', error);
				}
			});
		},

		/**
		 * Create Chart.js Chart
		 */
		createChart: function(canvas, data) {
			const ctx = canvas.getContext('2d');
			
			// Prepare data
			const labels = Object.keys(data);
			const values = Object.values(data);
			
			// Create chart
			this.chartInstance = new Chart(ctx, {
				type: 'bar',
				data: {
					labels: labels,
					datasets: [{
						label: accData.i18n.commentsDeleted,
						data: values,
						backgroundColor: 'rgba(16, 185, 129, 0.8)',
						borderColor: 'rgba(16, 185, 129, 1)',
						borderWidth: 2,
						borderRadius: 6,
						borderSkipped: false
					}]
				},
				options: {
					responsive: true,
					maintainAspectRatio: true,
					plugins: {
						legend: {
							display: false
						},
						tooltip: {
							backgroundColor: 'rgba(0, 0, 0, 0.8)',
							padding: 12,
							titleFont: {
								size: 14,
								weight: '600'
							},
							bodyFont: {
								size: 13
							},
							borderColor: 'rgba(255, 255, 255, 0.1)',
							borderWidth: 1,
							displayColors: false,
							callbacks: {
								title: function(context) {
									return context[0].label;
								},
								label: function(context) {
									return context.parsed.y + ' comments deleted';
								}
							}
						}
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								precision: 0,
								font: {
									size: 12
								},
								color: '#6b7280'
							},
							grid: {
								color: 'rgba(0, 0, 0, 0.05)'
							}
						},
						x: {
							ticks: {
								font: {
									size: 12
								},
								color: '#6b7280'
							},
							grid: {
								display: false
							}
						}
					}
				}
			});
		}
	};

})(jQuery);
