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

    public function index()
    {
        echo 'http://localhost/project-management-system/';
    }

    public function login()
    {
        $username = $this->input->post('username');
        $password = md5($this->input->post('password'));

        echo json_encode($this->RestModel->loginModel($username, $password));
        exit;
    }

    public function register()
    {
        $firstName = $this->input->post('firstName');
        $lastName = $this->input->post('lastName');
        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $c_password = $this->input->post('c_password');

        if ($password !== $c_password) {
            echo json_encode('Password does not match, please re-enter password again.');
            exit;
        } else if ($this->checkUsername($username) !== null) {
            echo json_encode('Username has been taken, please choose another username.');
            exit;
        } else {
            if ($this->RestModel->registerModel($firstName, $lastName, $username, md5($password)) === true) {
                $this->login($username, $password);
            } else {
                echo json_encode('Registration failed, please register again.');
                exit;
            }
        }
    }

    public function checkUsername($username)
    {
        return $this->RestModel->checkUsernameModel($username);
    }

    public function getWorkspace()
    {
        # code...
    }

    public function viewWorkspace()
    {
        # code...
    }

    public function setWorkspace()
    {
        # code...
    }

    public function addTask()
    {
        # code...
    }

    public function getAssigned()
    {
        # code...
    }

    public function viewAssigned()
    {
        # code...
    }

    public function getTeam()
    {
        # code...
    }

    public function addMember()
    {
        # code...
    }

    public function acceptMember()
    {
        # code...
    }

    public function rejectMember()
    {
        # code...
    }

    public function removeMember()
    {
        # code...
    }

    public function searchMember()
    {
        # code...
    }
}
