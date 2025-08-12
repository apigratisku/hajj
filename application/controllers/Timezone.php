<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timezone extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        echo "<h2>Timezone Settings</h2>";
        echo "<p>Current PHP timezone: " . date_default_timezone_get() . "</p>";
        echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p>GMT time: " . gmdate('Y-m-d H:i:s') . "</p>";
        echo "<p>Time difference from GMT: " . (strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'))) / 3600 . " hours</p>";
    }
} 