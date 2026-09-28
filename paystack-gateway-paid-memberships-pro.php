<?php
/**
 * Plugin Name: Paystack Gateway for Paid Memberships Pro
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/paystack-gateway/
 * Description: Plugin to add Paystack payment gateway into Paid Memberships Pro
 * Version: 1.9.2
 * Author: Paid Memberships Pro, Paystack
 * Author URI: https://www.paidmembershipspro.com
 * License: GPLv2 or later
 * Text Domain: paystack-gateway-paid-memberships-pro
 * Domain Path: /languages
 */
defined('ABSPATH') or die('No script kiddies please!');

// Constants
define( 'PMPRO_PAYSTACK_DIR', plugin_dir_path( __FILE__ ) );

require_once plugin_dir_path(__FILE__) . 'classes/paystack-tracker.php';
require_once plugin_dir_path(__FILE__) . 'classes/class.pmprogateway_paystack.php';

// Load plugin textdomain.
function pmpro_paystack_load_textdomain() {
	load_plugin_textdomain( 'pmpro-paystack', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'pmpro_paystack_load_textdomain' );

/**
 * This is for Set Expiration Date. Do we even need it?
 * /// Look at this one.
 */
function kkd_pmprosd_convert_date( $date ) {
// handle lower-cased y/m values.
$set_date = strtoupper($date);

// Change "M-" and "Y-" to "M1-" and "Y1-".
$set_date = preg_replace('/Y-/', 'Y1-', $set_date);
$set_date = preg_replace('/M-/', 'M1-', $set_date);

// Get number of months and years to add.
$m_pos = stripos( $set_date, 'M' );
$y_pos = stripos( $set_date, 'Y' );
if($m_pos !== false) {
$add_months = intval( pmpro_getMatches( '/M([0-9]*)/', $set_date, true ) );		
}
if($y_pos !== false) {
$add_years = intval( pmpro_getMatches( '/Y([0-9]*)/', $set_date, true ) );
}

// Allow new dates to be set from a custom date.
if(empty($current_date)) $current_date = current_time( 'timestamp' );

// Get current date parts.
$current_y = intval(date('Y', $current_date));
$current_m = intval(date('m', $current_date));
$current_d = intval(date('d', $current_date));

// Get set date parts.
$date_parts = explode( '-', $set_date);
$set_y = intval($date_parts[0]);
$set_m = intval($date_parts[1]);
$set_d = intval($date_parts[2]);

// Get temporary date parts.
$temp_y = $set_y > 0 ? $set_y : $current_y;
$temp_m = $set_m > 0 ? $set_m : $current_m;
$temp_d = $set_d;

// Add months.
if(!empty($add_months)) {
for($i = 0; $i < $add_months; $i++) {
	// If "M1", only add months if current date of month has already passed.
	if(0 == $i) {
		if($temp_d < $current_d) {
			$temp_m++;
			$add_months--;
		}
	} else {
		$temp_m++;
	}

	// If we hit 13, reset to Jan of next year and subtract one of the years to add.
	if($temp_m == 13) {
		$temp_m = 1;
		$temp_y++;
		$add_years--;
	}
}
}

// Add years.
if(!empty($add_years)) {
for($i = 0; $i < $add_years; $i++) {
	// If "Y1", only add years if current date has already passed.
	if(0 == $i) {
		$temp_date = strtotime(date("{$temp_y}-{$temp_m}-{$temp_d}"));
		if($temp_date < $current_date) {
			$temp_y++;
			$add_years--;
		}
	} else {
		$temp_y++;
	}
}
}

// Pad dates if necessary.
$temp_m = str_pad($temp_m, 2, '0', STR_PAD_LEFT);
$temp_d = str_pad($temp_d, 2, '0', STR_PAD_LEFT);

// Put it all together.
$set_date = date("{$temp_y}-{$temp_m}-{$temp_d}");

// Make sure we use the right day of the month for dates > 28
// From: http://stackoverflow.com/a/654378/1154321
$dotm = pmpro_getMatches('/\-([0-3][0-9]$)/', $set_date, true);
if ( $temp_m == '02' && intval($dotm) > 28 || intval($dotm) > 30 ) {
$set_date = date('Y-m-t', strtotime(substr($set_date, 0, 8) . "01"));
}



return $set_date;
}

