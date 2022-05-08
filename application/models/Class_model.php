<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Class_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($id = null, $classteacher = null) {

        $userdata = $this->customlib->getUserData();
        $role_id = $userdata["role_id"];
        $carray = array();

        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if ($classteacher == 'yes') {
               
                $classlist = $this->customlib->getclassteacher($userdata["id"]);
            } else {
               
                $classlist = $this->customlib->getClassbyteacher($userdata["id"]);
            }
        } else {

            $this->db->select()->from('classes');
            if ($id != null) {
                $this->db->where('id', $id);
            } else {
                $this->db->order_by('id');
            }
            $query = $this->db->get();
            if ($id != null) {
                $classlist = $query->row_array();
            } else {
                $classlist = $query->result_array();
            }
        }
            
        return $classlist;
    }

    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function remove($id) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('classes'); //class record delete.

        $this->db->where('class_id', $id);
        $this->db->delete('class_sections'); //class_sections record delete.

        $message      = DELETE_RECORD_CONSTANT." On classes id ".$id;
        $action       = "Delete";
        $record_id    = $id;
        $this->log($message, $record_id, $action);
		//======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /*Optional*/
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
        //return $return_value;
        }
    }

    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */
    public function add($data) {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('classes', $data);
        } else {
            $this->db->insert('classes', $data);
        }
    }

    function check_data_exists($data) {
        $this->db->where('class', $data);

        $query = $this->db->get('classes');
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return FALSE;
        }
    }

    public function class_exists($str) {

        $class = $this->security->xss_clean($str);
        $res = $this->check_data_exists($class);

        if ($res) {
            $pre_class_id = $this->input->post('pre_class_id');
            if (isset($pre_class_id)) {
                if ($res->id == $pre_class_id) {
                    return TRUE;
                }
            }
            $this->form_validation->set_message('class_exists', 'Record already exists');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function check_classteacher_exists($class,$section,$teacher)
    {
        
        //$this->db->where(array('class_id' =>$class ,'section_id' =>$section,'staff_id' =>$value));
        $this->db->where(array('class_id' =>$class ,'section_id' =>$section) );
        $this->db->where_in('staff_id',$teacher);
        

        $query = $this->db->get('class_teacher');
        if ($query->num_rows() > 0) {
    
          return $query->row();

        } else {
          
           return FALSE;
        } 
        
        exit();
    }

    public function class_teacher_exists($str){
       // print_r($_POST);
       // exit;
        $class = $this->input->post('class');
        $section = $this->input->post('section');
        $teachers = $this->input->post('teachers');
       // $class = $this->security->xss_clean($str);
        $res = $this->check_classteacher_exists($class,$section,$teachers);

        if ($res) {
            $pre_class_id = $this->input->post('pre_class_id');
            if (isset($pre_class_id)) {
                if ($res->id == $pre_class_id) {
                    return TRUE;
                }
            }
            $this->form_validation->set_message('class_exists', 'Record already exists');
            return FALSE;
        } else {
            return TRUE;
        }

    }

    public function getClassTeacher() {
//        $query = $this->db->select('staff.*,sections.section,classes.class,class_teacher.id as ctid,class_teacher.class_id,class_teacher.section_id')
              //           ->join("staff", "class_teacher.staff_id = staff.id")
              //          ->join("classes", "class_teacher.class_id = classes.id")
              //           ->join("sections", "class_teacher.section_id = sections.id")
              //          ->group_by("class_teacher.class_id, class_teacher.section_id")
              //           ->get("class_teacher");
              // $result = $query->result_array();
         
  $query = $this->db->query('SELECT distinct class_id AS class_id ,section_id,
                      (SELECT C.class FROM classes C WHERE C.ID = CT.CLASS_ID) class,
                     (SELECT S.SECTION FROM sections S  WHERE S.ID = CT.SECTION_ID) section
                     FROM class_teacher CT where 1=1');
        $result = $query->result_array();

        return $result;
    }

    public function get_section($id){

       return $this->db->select('sections.id,sections.section')->from('class_sections')->join('sections','class_sections.section_id=sections.id')->where('class_id',$id)->get()->result_array();

    }

}
