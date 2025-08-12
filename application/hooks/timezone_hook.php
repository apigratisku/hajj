<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Set timezone to GMT+8
 * This function is called before the CodeIgniter system is fully initialized
 */
function set_timezone() {
    // Set timezone to GMT+8 (Singapore/Malaysia/Philippines/Hong Kong/Western Australia)
    date_default_timezone_set('Asia/Singapore');
} 