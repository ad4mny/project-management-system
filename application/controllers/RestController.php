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
        $userID = $this->input->post('userID');

        echo json_encode($this->RestModel->getWorkspaceModel($userID));
        exit;
    }

    public function viewWorkspace()
    {
        $workspaceID = $this->input->post('workspaceID');

        echo json_encode($this->RestModel->viewWorkspaceModel($workspaceID));
        exit;
    }

    public function removeWorkspace()
    {
        $workspaceID = $this->input->post('workspaceID');

        echo json_encode($this->RestModel->removeWorkspaceModel($workspaceID));
        exit;
    }

    public function setWorkspace()
    {
        $userID = $this->input->post('userID');
        $workspaceName = $this->input->post('workspaceName');
        $workspaceDesc = $this->input->post('workspaceDesc');
        $startDate = $this->input->post('startDate');
        $endDate = $this->input->post('endDate');
        $workspaceMember = json_decode($this->input->post('workspaceMember'), true);
        $taskName = $this->input->post('taskName');
        $taskMember = json_decode($this->input->post('taskMember'), true);

        echo json_encode($this->RestModel->setWorkspaceModel($userID, $workspaceName, $workspaceDesc, $startDate, $endDate, $workspaceMember, $taskName, $taskMember));
        exit;
    }

    public function getAssigned()
    {
        $userID = $this->input->post('userID');

        echo json_encode($this->RestModel->getAssignedModel($userID));
        exit;
    }

    public function setTeam()
    {
        $userID = $this->input->post('userID');

        echo json_encode($this->RestModel->setTeamModel($userID));
        exit;
    }

    public function getFriend()
    {
        $userID = $this->input->post('userID');

        echo json_encode($this->RestModel->getFriendModel($userID));
        exit;
    }

    public function removeTeam()
    {
        $teamID = $this->input->post('teamID');

        echo json_encode($this->RestModel->removeTeamModel($teamID));
        exit;
    }

    public function getRequest()
    {
        $teamID = $this->input->post('teamID');

        echo json_encode($this->RestModel->getRequestModel($teamID));
        exit;
    }

    public function addMember()
    {
        $userID = $this->input->post('userID');
        $teamID = $this->input->post('teamID');

        echo json_encode($this->RestModel->addMemberModel($userID, $teamID));
        exit;
    }

    public function approveMember()
    {
        $userID = $this->input->post('userID');
        $teamID = $this->input->post('teamID');

        echo json_encode($this->RestModel->approveMemberModel($userID, $teamID));
        exit;
    }

    public function removeMember()
    {
        $userID = $this->input->post('userID');

        echo json_encode($this->RestModel->removeMemberModel($userID));
        exit;
    }

    public function searchUser()
    {
        $query = $this->input->post('query');

        echo json_encode($this->RestModel->searchUserModel($query));
        exit;
    }

    public function updateProfile()
    {
        $userID = $this->input->post('userID');
        $firstName = $this->input->post('firstName');
        $lastName = $this->input->post('lastName');
        $username = $this->input->post('username');

        if ($this->checkUsername($username) !== null) {
            echo json_encode('Username has been taken, please choose another username.');
            exit;
        } else {
            if ($this->RestModel->updateProfileModel($userID, $firstName, $lastName, $username) === true) {
                echo json_encode(array($firstName, $lastName, $username));
                exit;
            } else {
                echo json_encode('Update profile failed, please register again.');
                exit;
            }
        }
    }
}
