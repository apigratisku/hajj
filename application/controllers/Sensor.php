<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sensor extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }

    //Fungsi Auto Update Data Sensor
    public function sensor_plc()
    {
        $this->load->view('sensor/sensor_plc');
        
    }
    public function flow() {
        $this->load->view('sensor/flow');
    }
}
