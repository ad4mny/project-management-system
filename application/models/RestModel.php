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

        $this->db->select('GROUP_CONCAT(taskName) as taskName, GROUP_CONCAT(firstName) as firstName, GROUP_CONCAT(assigns.userID) as userID');
        $this->db->from('tasks');
        $this->db->join('assigns', 'assigns.taskID = tasks.taskID');
        $this->db->join('users', 'users.userID = assigns.userID');
        $this->db->group_by('tasks.taskID');
        $this->db->where('workspaceID', $workspaceID);

        array_push($data, $this->db->get()->result_array());

        return $data;
    }

    public function removeWorkspaceModel($workspaceID)
    {
        $this->db->where('workspaceID', $workspaceID);
        return $this->db->delete('workspaces');
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
                                'taskID' => $data[$value['task'] - 1]['taskID'],
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
        $this->db->select('taskID');
        $this->db->from('assigns');
        $this->db->where('userID', $userID);
        $result = $this->db->get()->result_array();

        if ($result != false || !empty($result)) {
            $taskIDs = [];

            foreach ($result as $row) {
                array_push($taskIDs, $row['taskID']);
            }

            $this->db->select('GROUP_CONCAT(taskName) as taskName, GROUP_CONCAT(workspaceName) as workspaceName, GROUP_CONCAT(firstName) as firstName, GROUP_CONCAT(startDate) as startDate, GROUP_CONCAT(endDate) as endDate, GROUP_CONCAT(assigns.userID) as userID');
            $this->db->from('assigns');
            $this->db->join('tasks', 'tasks.taskID = assigns.taskID');
            $this->db->join('users', 'users.userID = assigns.userID');
            $this->db->join('workspaces', 'workspaces.workspaceID = tasks.workspaceID');
            $this->db->where_in('assigns.taskID', $taskIDs);
            $this->db->group_by('assigns.taskID');
            return $this->db->get()->result_array();
        } else {
            return false;
        }
    }

    public function getFriendListModel($userID)
    {
        $this->db->select('users.userID, users.firstName, users.lastName');
        $this->db->from('friends');
        $this->db->join('users', 'users.userID = friends.requestID');
        $this->db->where('friends.status', 1);
        $this->db->where('friends.userID', $userID);
        return $this->db->get()->result_array();
    }

    public function getFriendRequestModel($userID)
    {
        $this->db->select('users.userID, users.firstName, users.lastName');
        $this->db->from('friends');
        $this->db->join('users', 'users.userID = friends.userID');
        $this->db->where('friends.status', 0);
        $this->db->where('friends.requestID', $userID);
        return $this->db->get()->result_array();
    }

    public function addFriendModel($userID, $friendID)
    {
        $this->db->select('friendID');
        $this->db->from('friends');
        $this->db->where('userID', $userID);
        $this->db->where('requestID', $friendID);
        $result = $this->db->get()->row_array();

        if ($result == false || empty($result)) {

            $data = array(
                'userID' => $friendID,
                'requestID' => $userID,
                'status' => 0
            );

            return $this->db->insert('friends', $data);
        } else {
            return false;
        }
    }

    public function approveFriendModel($userID, $friendID)
    {
        $this->db->select('friendID');
        $this->db->from('friends');
        $this->db->where('userID', $userID);
        $this->db->where('requestID', $friendID);
        $result = $this->db->get()->row_array();

        if ($result == false || empty($result)) {

            $data = array(
                'userID' => $userID,
                'requestID' => $friendID,
                'status' => 1
            );

            $this->db->insert('friends', $data);

            if ($this->db->affected_rows() > 0) {

                $data = array(
                    'status' => 1
                );

                $this->db->where('userID', $friendID);
                $this->db->where('requestID', $userID);
                return $this->db->update('friends', $data);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function removeFriendModel($userID, $friendID)
    {
        $this->db->where('userID', $userID);
        $this->db->where('requestID', $friendID);
        return $this->db->delete('friends');
    }

    public function searchUserModel($userID, $query)
    {
        $this->db->select('userID, firstName, lastName');
        $this->db->from('users');
        $this->db->where('userID !=', $userID);
        $this->db->like('firstName', $query);
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
