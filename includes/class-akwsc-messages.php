<?php

/**
 * Class for storing plugin messages.
 *
 * @link       https://github.com/akashSinghtkd
 * @since      1.0.0
 *
 * @package    Akwsc
 * @subpackage Akwsc/includes
 */

/**
 * Class for storing plugin messages.
 *
 * Centralizes all messages used in the plugin to ensure consistency
 * and ease of maintenance. This class provides static methods to
 * retrieve messages for various scenarios.
 *
 * @package    Akwsc
 * @subpackage Akwsc/includes
 * @author     Akash Singh <akashsinghtkd01@gmail.com>
 */
class Akwsc_Plugin_Messages
{

    /**
     * Get the message for WooCommerce not installed.
     *
     * @since    1.0.0
     * @return string
     */
    public static function woocommerce_not_installed()
    {
        return sprintf(
            esc_html__('The %s plugin requires WooCommerce to be installed. Please install the WooCommerce plugin to use %s features.', AKWSC_TEXT_DOMAIN),
            AKWSC_PLUGIN_FULL_NAME,
            AKWSC_PLUGIN_FULL_NAME
        );
    }

    /**
     * Get the message for WooCommerce not activated.
     *
     * @since    1.0.0
     * @return string
     */
    public static function woocommerce_not_activated()
    {
        return sprintf(
            esc_html__('WooCommerce is installed but not activated. Please activate the WooCommerce plugin to use %s features.', AKWSC_TEXT_DOMAIN),
            AKWSC_PLUGIN_FULL_NAME
        );
    }

    /**
     * Retrieves a error message based on the provided error key.
     *
     * This function returns a localized and formatted error message corresponding to the given error key.
     * If the error key is associated with a dynamic message (e.g., required data), it will format the message
     * with the provided data. If the error key is not recognized, a generic unexpected error message is returned.
     *
     * @since 1.0.0
     * @param string $error_key 
     * @param array  $data 
     * @return string The localized and formatted error message.
     */
    public static function get_error_message($error_key, $data = [])
    {
        // Define default error messages
        $error_messages = array(
            'invalid_request'           => __('Invalid request.', AKWSC_TEXT_DOMAIN),
            'nonce_failed'              => __('Nonce verification failed.', AKWSC_TEXT_DOMAIN),
            'invalid_params'            => __('Invalid product ID or requested quantity.', AKWSC_TEXT_DOMAIN),
            'product_not_found'         => __('Product not found.', AKWSC_TEXT_DOMAIN),
            'enter_valid_number'        => __('Please enter a valid quantity greater than 0.', AKWSC_TEXT_DOMAIN),
            'group_product_not_valid'   => __('Please ensure at least one product quantity is greater than 0.', AKWSC_TEXT_DOMAIN),
            'product_id_invalid'        => __('Product ID is missing or invalid.', AKWSC_TEXT_DOMAIN),
            'unexpected_error'          => __('An unexpected error occurred. Please try again later.', AKWSC_TEXT_DOMAIN),
            'variable_not_found'       => __('Please select the variable option.', AKWSC_TEXT_DOMAIN),
        );

        if ($error_key == 'required_data') {
            if (is_array($data) && !empty($data)) {
                $error_messages[$error_key] = sprintf(__('The following fields are required: %s.', AKWSC_TEXT_DOMAIN), implode(', ', $data));
            } else {
                $error_messages[$error_key] = __('Some required parameters are missing.', AKWSC_TEXT_DOMAIN);
            }
        }

        if ($error_key == 'empty_data') {
            if (is_array($data) && !empty($data)) {
                $error_messages[$error_key] = sprintf(__('%s field are empty. Please fill the value', AKWSC_TEXT_DOMAIN), implode(', ', $data));
            } else {
                $error_messages[$error_key] = __('Some required parameters are missing.', AKWSC_TEXT_DOMAIN);
            }
        }

        // Return the error message or a default unexpected error message
        return isset($error_messages[$error_key]) ? $error_messages[$error_key] : $error_messages['unexpected_error'];
    }

    /**
     * Retrieve the appropriate message based on the stock check ajax action.
     *
     * This function returns a message corresponding to the given key.
     *
     * @since 1.0.0
     *
     * @param string $key
     * @param int    $stock_quantity  Optional. The number of units in stock. Default is an empty string.
     *
     * @return string The corresponding message for the provided key.
     */
    public static function get_message_for_stock_check_action_ajax($key, $stock_quantity = '' )
    {
        // Define default messages
        $message = array(
            'stock_available'           => __('Great! We have enough stock.', AKWSC_TEXT_DOMAIN),
            'limited_stock_available'   => __('Sorry, we only have '.$stock_quantity.' units in stock.', AKWSC_TEXT_DOMAIN),
            'out_of_stock'              => __('This product is currently out of stock.', AKWSC_TEXT_DOMAIN),
        );

        return isset($message[$key]) ? $message[$key] : '';
    }
}
