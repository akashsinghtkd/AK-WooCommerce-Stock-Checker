(function( $ ) {
	'use strict';
	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// Object to keep track of active AJAX requests by product ID
	let activeRequests = {};

	/**
	 * Sends an AJAX request.
	 *
	 * @param {string} method - The HTTP method to use for the request (e.g., 'POST', 'GET').
	 * @param {string} action - The action hook for the AJAX request, used to identify the action in WordPress.
	 * @param {object} data - The data to send with the request. Will be serialized automatically.
	 * @param {function} successCallback - The function to call if the request succeeds. Receives the response data.
	 * @param {function} errorCallback - The function to call if the request fails. Receives the error.
	 */
	function sendAjaxRequest(method, action, data, successCallback = '', errorCallback = '') {
		// Add the action to the data object
		data.action = action;
		// Add the nonce to the data object
		data.nonce = akwsc.nonce;

		// Send the AJAX request
		activeRequests[data.product_id] = $.ajax({
			url: akwsc.ajaxurl,
			method: method,
			data: data,
			success: function(response) {
				// Call the success callback if provided
				if (typeof successCallback === 'function') {
					successCallback(response);
				}
			},
			error: function(xhr, status, error) {
				// Call the error callback if provided
				if (typeof errorCallback === 'function') {
					errorCallback(xhr, status, error);
				} else {
					// Default error handling
					console.error('AJAX Error:', status, error);
				}
			},
			complete: function() {
				// Remove the completed request from activeRequests
				delete activeRequests[data.product_id];
			}
		});
	}

	/**
	 * Handles the click event for the "Check Availability" button.
	 * 
	 * @since 1.0.0
	 */
	$(document).on('click', '#akwsc-check-availability-button', function() {
		const $button = $(this);
		const $parent = $button.parent();
		const $form = $button.parents('form');
		
		let product_id = $button.data('akwsc-product-id');
		let qty;
		let data;
	
		// Get elements
		const $loadingText = $parent.find('#akwsc-loading-text');
		const $messageBox = $parent.find('#akwsc-message-box');
	
		// Determine product type and retrieve data accordingly
		const product_type = $button.data('akwsc-product-type');
		const is_single_page = $button.data('akwsc-is-product-detail-page');

		if (product_type === 'grouped') {
			const groupedProducts = [];
	
			$form.find('input[name^="quantity["]').each(function() {
				const $input = $(this);
				const inputQty = parseInt($input.val(), 10);
	
				  // Extract product id from the name attribute using a regular expression
				  const nameAttr = $input.attr('name');
				  const keyMatch = nameAttr.match(/\[(\d+)\]/);
				  const productId = keyMatch ? keyMatch[1] : null;
	
				if (productId && !isNaN(inputQty) && inputQty > 0) {
					groupedProducts.push({ product_id: productId, requested_qty: inputQty });
				}
			});
	
			if (groupedProducts.length === 0) {
				$messageBox.html('<span class="error-message">'+akwsc.message.group_product_not_valid+'</span>');
				return;
			}
	
			data = { products: groupedProducts, product_id: product_id, type: product_type};
	
		} else {
			if (product_type === 'variable') {
				product_id = $form.find('input[name="variation_id"]').val();
				if (!product_id || product_id === '0') {
					$messageBox.html('<span class="error-message">'+akwsc.message.variable_not_found+'</span>');
					return;
				}
			}
	
			if (!product_id || product_id === '0') {
				$messageBox.html('<span class="error-message">'+akwsc.message.product_id_invalid+'</span>');
				return;
			}
	
			qty = is_single_page 
				? parseInt($form.find('input[name="quantity"]').val(), 10) 
				: parseInt($parent.find('input[name="akwsc_quantity_input"]').val(), 10);
	
			if (isNaN(qty) || qty <= 0) {
				$messageBox.html('<span class="error-message">'+akwsc.message.enter_valid_number+'</span>');
				return;
			}
	
			data = { product_id: product_id, requested_qty: qty, type: product_type};
		}
	
		// Check for active AJAX requests and abort if necessary
		if (activeRequests[product_id]) {
			activeRequests[product_id].abort();
		}
	
		// Display loading text and clear previous messages
		$loadingText.show();
		$messageBox.html('');
	
		// Callback for successful AJAX request
		const callback = function(response) {
			$loadingText.hide();
			$messageBox.html(response.success 
				? `<span class="success-message">${response.data.message}</span>` 
				: `<span class="error-message">${response.data.message}</span>`);
		};
	
		// Callback for failed AJAX request
		const error_callback = function() {
			$loadingText.hide();
			$messageBox.html('<span class="error-message">'+akwsc.message.unexpected_error+'</span>');
		};
	
		// Send AJAX request
		sendAjaxRequest('post', akwsc.action.stock_check, data, callback, error_callback);
	});

})( jQuery );
