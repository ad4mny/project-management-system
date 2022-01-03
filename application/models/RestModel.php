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

    public function getWorkspaceModel()
    {
        # code...
    }

    public function viewWorkspaceModel()
    {
        # code...
    }

    public function setWorkspaceModel()
    {
        # code...
    }

    public function addTaskModel()
    {
        # code...
    }

    public function getAssignedModel()
    {
        # code...
    }

    public function viewAssignedModel()
    {
        # code...
    }

    public function getTeamModel()
    {
        # code...
    }

    public function addMemberModel()
    {
        # code...
    }

    public function acceptMemberModel()
    {
        # code...
    }

    public function rejectMemberModel()
    {
        # code...
    }

    public function removeMemberModel()
    {
        # code...
    }

    public function searchMemberModel()
    {
        # code...
    }
}
