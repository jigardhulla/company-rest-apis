<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Department_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function getDepartments()
    {
        // Retrieve all departments from the database
        $this->db->select(array('id','name','description'));
        $this->db->where('deleted', '0');
        return $this->db->get('departments')->result();
    }

    public function createDepartment($data)
    {
        // Create a new department in the database
        $this->db->insert('departments', $data);
        // Return the ID of the created department
        return $this->db->insert_id();
    }

    public function getDepartmentById($id)
    {
        // Retrieve a department by its ID from the database
        $this->db->select(array('id','name','description'));
        $this->db->where('deleted', '0');
        return $this->db->get_where('departments', array('id' => $id))->row();
    }

    public function getDepartmentByName($name)
    {
        // Retrieve a department by its Name from the database
        $this->db->select(array('id','name','description'));
        $this->db->where('deleted', '0');
        return $this->db->get_where('departments', array('name' => $name))->row();
    }

    public function deleteDepartment($id)
    {
        $this->db->trans_start(); // Start a database transaction

        // Delete the employees associated with the department
        $this->db->set('deleted', '1');
        $this->db->where('department_id', $id);
        $this->db->update('employees');

        // Delete the department
        $this->db->set('deleted', '1');
        $this->db->where('id', $id);
        $this->db->update('departments');

        $this->db->trans_complete(); // Complete the database transaction

        return $this->db->trans_status(); // Return the transaction status
    }

}
