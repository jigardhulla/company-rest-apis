<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function getEmployees($keyword = "")
    {
        // Retrieve all Employees from the database
        $this->db->select('employees.id, employees.name, employees.email, employees.department_id');
        $this->db->from('employees');
        $this->db->where('deleted', '0');
        if ($keyword) {
            $this->db->like('name', $keyword);
            $this->db->or_like('email', $keyword);
        }
        $query = $this->db->get();
        $employeeData = $query->result();
        $employees = [];

        foreach ($employeeData as $row) {
            $employeeId = $row->id;
            $employees[$employeeId] = [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'department_id' => $row->department_id,
                'contact_numbers' => [],
                'addresses' => []
            ];

            // Retrieve contact numbers
            $this->db->select('contact_number');
            $this->db->from('employee_contacts');
            $this->db->where('deleted', '0');
            $this->db->where('employee_id', $employeeId);
            $contactQuery = $this->db->get();
            $contactNumbers = $contactQuery->result();

            foreach ($contactNumbers as $contact) {
                $employees[$employeeId]['contact_numbers'][] = $contact->contact_number;
            }

            // Retrieve addresses
            $this->db->select('address');
            $this->db->from('employee_addresses');
            $this->db->where('deleted', '0');
            $this->db->where('employee_id', $employeeId);
            $addressQuery = $this->db->get();
            $addresses = $addressQuery->result();

            foreach ($addresses as $address) {
                $employees[$employeeId]['addresses'][] = $address->address;
            }
        }

        return array_values($employees);
    }

    public function createEmployee($data)
    {
        // Create a new Employee in the database
        $this->db->insert('employees', $data);
        // Return the ID of the created Employee
        return $this->db->insert_id();
    }

    public function getEmployeeById($id)
    {
        // Retrieve a Employee by its ID from the database
        $this->db->select('employees.id, employees.name, employees.email, employees.department_id');
        $this->db->from('employees');
        $this->db->where('deleted', '0');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $employeeData = $query->result();
        $employees = [];

        foreach ($employeeData as $row) {
            $employeeId = $row->id;
            $employees[$employeeId] = [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'department_id' => $row->department_id,
                'contact_numbers' => [],
                'addresses' => []
            ];

            // Retrieve contact numbers
            $this->db->select('contact_number');
            $this->db->from('employee_contacts');
            $this->db->where('deleted', '0');
            $this->db->where('employee_id', $employeeId);
            $contactQuery = $this->db->get();
            $contactNumbers = $contactQuery->result();

            foreach ($contactNumbers as $contact) {
                $employees[$employeeId]['contact_numbers'][] = $contact->contact_number;
            }

            // Retrieve addresses
            $this->db->select('address');
            $this->db->from('employee_addresses');
            $this->db->where('deleted', '0');
            $this->db->where('employee_id', $employeeId);
            $addressQuery = $this->db->get();
            $addresses = $addressQuery->result();

            foreach ($addresses as $address) {
                $employees[$employeeId]['addresses'][] = $address->address;
            }
        }

        return array_values($employees);
    }

    public function getEmployeeByEmail($email)
    {
        // Retrieve a Employee by its email from the database
        $this->db->where('deleted', '0');
        return $this->db->get_where('employees', array('email' => $email))->row();
    }
    
    public function addContactNumber($employeeId, $contactNumber)
    {
        // Create a new Employee contact in the database
        $this->db->insert('employee_contacts', array('employee_id' => $employeeId, 'contact_number' => $contactNumber));
        // Return the ID of the created Employee
        return $this->db->insert_id();
    }
    
    public function addAddress($employeeId, $address)
    {
        // Create a new Employee address in the database
        $this->db->insert('employee_addresses', array('employee_id' => $employeeId, 'address' => $address));
        // Return the ID of the created Employee
        return $this->db->insert_id();
    }

    public function deleteEmployee($id)
    {
        $this->db->trans_start(); // Start a database transaction

        // Delete the employees contacts associated with the employee
        $this->db->set('deleted', '1');
        $this->db->where('employee_id', $id);
        $this->db->update('employee_contacts');

        // Delete the employees addresses associated with the employee
        $this->db->set('deleted', '1');
        $this->db->where('employee_id', $id);
        $this->db->update('employee_addresses');

        // Delete the employee
        $this->db->set('deleted', '1');
        $this->db->where('id', $id);
        $this->db->update('employees');

        $this->db->trans_complete(); // Complete the database transaction

        return $this->db->trans_status(); // Return the transaction status
    }

    public function updateEmployee($employeeId, $employeeData) {
        $this->db->trans_start(); // Start a database transaction

        // Delete the employees contacts associated with the employee
        $this->db->set('deleted', '1');
        $this->db->where('employee_id', $employeeId);
        $this->db->update('employee_contacts');

        // Delete the employees addresses associated with the employee
        $this->db->set('deleted', '1');
        $this->db->where('employee_id', $employeeId);
        $this->db->update('employee_addresses');

        // Add contact numbers
        if (isset($employeeData['contact_numbers'])) {
            foreach ($employeeData['contact_numbers'] as $contactNumber) {
                $this->addContactNumber($employeeId, $contactNumber);
            }
        }

        // Add addresses
        if (isset($employeeData['addresses'])) {
            foreach ($employeeData['addresses'] as $address) {
                $this->addAddress($employeeId, $address);
            }
        }

        // Update employee details
        $employeeToUpdate = array(
            'name' => $employeeData['name'],
            'department_id' => $employeeData['department_id']
        );
        $this->db->where('id', $employeeId);
        $this->db->update('employees', $employeeToUpdate);

        $this->db->trans_complete(); // Complete the database transaction

        return $this->db->trans_status(); // Return the transaction status
    }

}
