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
        $data[0] = [];
        $data[1] = [];
        $this->db->select('*');
        $this->db->from('workspaces');
        $this->db->where('userID', $userID);

        $data[0] = $this->db->get()->result_array();

        foreach ($data[0] as $row) {
            $this->db->select('*');
            $this->db->from('teams');
            $this->db->join('users', 'users.userID = teams.userID');
            $this->db->where('teams.workspaceID', $row['workspaceID']);
            array_push($data[1], $this->db->get()->result_array());
        }

        return $data;
    }

    public function viewWorkspaceModel($workspaceID)
    {
        $data = [];

        $this->db->select('*');
        $this->db->from('workspaces');
        $this->db->where('workspaceID', $workspaceID);

        array_push($data, $this->db->get()->result_array());

        $this->db->select('*');
        $this->db->from('teams');
        $this->db->join('users', 'users.userID = teams.userID');
        $this->db->where('teams.workspaceID', $workspaceID);

        array_push($data, $this->db->get()->result_array());

        $this->db->select('*');
        $this->db->from('tasks');
        $this->db->where('workspaceID', $workspaceID);

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

    public function setWorkspaceModel($userID, $workspaceName, $workspaceDesc, $startDate, $endDate, $workspaceMember, $taskName, $taskMember)
    {
        $workspaces = array(
            'userID' => $userID,
            'workspaceName' => $workspaceName,
            'workspaceDesc' => $workspaceDesc,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'log' => date('Y/m/d H:i:s e')
        );

        // Add workspace information
        $this->db->insert('workspaces', $workspaces);

        if ($this->db->affected_rows() > 0) {

            $workspaceID = $this->db->insert_id();
            $teams = [];

            foreach ($workspaceMember as $key => $value) {
                array_push($teams, array(
                    'workspaceID' => $workspaceID,
                    'userID' => $value['userid'],
                    'log' => date('Y/m/d H:i:s e')
                ));
            }

            // Add workspace team member
            $this->db->insert_batch('teams', $teams);

            if ($this->db->affected_rows() > 0) {

                // Check if workspace have additional task
                if ($taskName != null) {

                    $tasks = [];

                    foreach ($taskName as $task) {
                        array_push($tasks, array(
                            'workspaceID' => $workspaceID,
                            'taskName' => $task,
                            'log' => date('Y/m/d H:i:s e')
                        ));
                    }

                    // Add workspace tasks
                    $this->db->insert_batch('tasks', $tasks);

                    if ($this->db->affected_rows() > 0) {

                        $this->db->select('taskID');
                        $this->db->from('tasks');
                        $this->db->where('workspaceID', $workspaceID);
                        $data = $this->db->get()->result_array();

                        $assigns = [];

                        foreach ($taskMember as $key => $value) {
                            array_push($assigns, array(
                                'taskID' => $data[$key]['taskID'],
                                'userID' => $value['userid'],
                                'log' => date('Y/m/d H:i:s e')
                            ));
                        }
                        
                        // Add task member assigned
                        return $this->db->insert_batch('assigns', $assigns);
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

    public function getFriendModel($userID)
    {
        $this->db->select('users.userID, users.firstName, users.lastName');
        $this->db->from('friends');
        $this->db->join('users', 'users.userID = friends.requestID');
        $this->db->where('friends.status', 1);
        $this->db->where('friends.userID', $userID);
        return $this->db->get()->result_array();
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

    public function updateProfileModel($userID, $firstName, $lastName, $username)
    {
        $data = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'username' => $username,
            'log' => date('Y/m/d H:i:s e')
        );

        $this->db->where('userID', $userID);
        return $this->db->update('users', $data);
    }
}
