<?php
header("Access-Control-Allow-Origin: *");
defined('BASEPATH') or exit('No direct script access allowed');

class RestController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('RestModel');
    }
}
