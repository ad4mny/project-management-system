<?php
defined('BASEPATH') or exit('No direct script access allowed');

class RestModel extends CI_Model
{
    public function loginModel($username, $password)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('username', $username);
        $this->db->where('password', $password);
        return $this->db->get()->row_array();
    }

    public function registerModel($firstName, $lastName, $username, $password)
    {
        $data = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'username' => $username,
            'password' => $password,
            'log' => date('Y/m/d H:i:s e')
        );

        return $this->db->insert('users', $data);
    }

    public function checkUsernameModel($username)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('username', $username);
        return $this->db->get()->row_array();
    }

    public function getWorkspaceModel($userID)
    {
        $data = [];
        $this->db->select('*');
        $this->db->from('projects');
        $this->db->where('projects.userID', $userID);

        $data['project'] = $this->db->get()->result_array();

        foreach ($data['project'] as $row) {
            $this->db->select('*');
            $this->db->from('projectMembers');
            $this->db->join('users', 'users.userID = projectMembers.userID');
            $this->db->where('projectMembers.projectID', $row['projectID']);
            array_push($data, $this->db->get()->result_array());
        }

        return $data;
    }

    public function viewWorkspaceModel($projectID)
    {
        $data = [];
        $this->db->select('*');
        $this->db->from('projects');
        $this->db->where('projectID', $projectID);

        array_push($data, $this->db->get()->result_array());

        $this->db->select('*');
        $this->db->from('projectMembers');
        $this->db->join('users', 'users.userID = projectMembers.userID');
        $this->db->where('projectMembers.projectID', $projectID);
        array_push($data, $this->db->get()->result_array());

        $this->db->select('*');
        $this->db->from('tasks');
        $this->db->where('projectID', $projectID);
        array_push($data, $this->db->get()->result_array());

        $taskID = [];

        foreach ($data[2] as $key => $value) {
            array_push($taskID, $value['taskID']);
        }

        if ($taskID != null) {
            $this->db->select('*');
            $this->db->from('assigns');
            $this->db->join('users', 'users.userID = assigns.userID');
            $this->db->where_in('taskID', $taskID);
            array_push($data, $this->db->get()->result_array());
        }

        return $data;
    }

    public function removeWorkspaceModel($projectID)
    {
        $this->db->where('projectID', $projectID);
        return $this->db->delete('projects');
    }

    public function setWorkspaceModel($userID, $projectName, $projectDesc, $startDate, $endDate, $projectMember, $taskName, $taskMember)
    {
        $projectData = array(
            'userID' => $userID,
            'projectName' => $projectName,
            'projectDesc' => $projectDesc,
            'projectStartDate' => $startDate,
            'projectEndDate' => $endDate,
            'log' => date('Y/m/d H:i:s e')
        );

        if ($this->db->insert('projects', $projectData)) {

            $projectID = $this->db->insert_id();
            $projectMembers = [];

            foreach ($projectMember as $key => $value) {
                array_push($projectMembers, array(
                    'projectID' => $projectID,
                    'userID' => $value['id'],
                    'log' => date('Y/m/d H:i:s e')
                ));
            }

            if ($this->db->insert_batch('projectMembers', $projectMembers)) {

                if ($taskName != null) {

                    $taskData = [];

                    foreach ($taskName as $task) {
                        array_push($taskData, array(
                            'projectID' => $projectID,
                            'taskName' => $task,
                            'log' => date('Y/m/d H:i:s e')
                        ));
                    }

                    if ($this->db->insert_batch('tasks', $taskData)) {

                        $this->db->select('taskID');
                        $this->db->from('tasks');
                        $this->db->where('projectID', $projectID);
                        $data = $this->db->get()->result_array();

                        $assignsData = [];

                        foreach ($taskMember as $key => $value) {
                            array_push($assignsData, array(
                                'taskID' => $data[$key]['taskID'],
                                'userID' => $value['id'],
                                'log' => date('Y/m/d H:i:s e')
                            ));
                        }

                        return $this->db->insert_batch('assigns', $assignsData);
                    }
                } else {
                    return true;
                }
            }
        }
    }

    public function getAssignedModel($userID)
    {
        $this->db->select('GROUP_CONCAT(taskName) as taskName, GROUP_CONCAT(projectName) as projectName, GROUP_CONCAT(firstName) as firstName, GROUP_CONCAT(projectStartDate) as projectStartDate, GROUP_CONCAT(projectEndDate) as projectEndDate');
        $this->db->from('assigns');
        $this->db->join('tasks', 'tasks.taskID = assigns.taskID');
        $this->db->join('users', 'users.userID = assigns.userID');
        $this->db->join('projects', 'projects.projectID = tasks.projectID');
        $this->db->where('assigns.userID', $userID);
        $this->db->group_by('assigns.taskID');
        return $this->db->get()->result_array();
    }

    public function setTeamModel($userID)
    {
        $data = array(
            'userID' => $userID,
            'log' => date('Y/m/d H:i:s e')
        );

        if ($this->db->insert('teams', $data)) {
            $teamID = $this->db->insert_id();
            $data = array(
                'teamID' => $teamID,
                'teamRequest' => 0
            );

            $this->db->where('userID', $userID);
            $this->db->update('users', $data);
        }

        return $teamID;
    }

    public function getTeamModel($teamID)
    {
        $data = [];
        $this->db->select('userID');
        $this->db->from('teams');
        $this->db->where('teamID', $teamID);
        $data = $this->db->get()->row_array();

        $this->db->select('userID, firstName, lastName');
        $this->db->from('users');
        $this->db->where('teamID', $teamID);
        array_push($data, $this->db->get()->result_array());

        return $data;
    }

    public function removeTeamModel($teamID)
    {
        $this->db->select('userID');
        $this->db->from('users');
        $this->db->where('teamID', $teamID);
        $result = $this->db->get()->result_array();

        $this->db->where('teamID', $teamID);
        $this->db->delete('teams');

        $data = array(
            'teamRequest' => NULL
        );

        foreach ($result as $row) {
            $this->db->where('userID', $row['userID']);
            $this->db->update('users', $data);
        }

        return true;
    }

    public function getRequestModel($teamID)
    {
        $data = [];
        $this->db->select('userID');
        $this->db->from('teams');
        $this->db->where('teamID', $teamID);
        $data = $this->db->get()->row_array();

        $this->db->select('userID, firstName, lastName');
        $this->db->from('users');
        $this->db->where('teamRequest', $teamID);
        array_push($data, $this->db->get()->result_array());

        return $data;
    }

    public function addMemberModel($userID, $teamID)
    {
        $data = array(
            'teamID' => $teamID,
            'teamRequest' => 0
        );

        $this->db->where('userID', $userID);
        return $this->db->update('users', $data);
    }

    public function approveMemberModel($userID, $teamID)
    {
        $data = array(
            'teamID' => $teamID,
            'teamRequest' => 0
        );

        $this->db->where('userID', $userID);
        return $this->db->update('users', $data);
    }

    public function removeMemberModel($userID)
    {
        $data = array(
            'teamID' => NULL,
            'teamRequest' => NULL
        );

        $this->db->where('userID', $userID);
        return $this->db->update('users', $data);
    }

    public function searchUserModel($query)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->like('firstName', $query);
        $this->db->where('teamID', NULL);
        $this->db->where('teamRequest', NULL);
        return $this->db->get()->result_array();
    }
}
