<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/tcpdf/tcpdf.php';

// Check if TCPDF class exists
if (!class_exists('TCPDF')) {
    // Fallback to simple implementation
    require_once APPPATH . 'third_party/tcpdf/tcpdf.php';
}

class Pdf extends TCPDF {
    
    public function __construct() {
        parent::__construct();
    }
}
