<?php

class Classteacher_model extends MY_Model {

    public function getClassTeacher($id = null) {

        if (!empty($id)) {

            $query = $this->db->select('staff.*,class_teacher.id as ctid,class_teacher.class_id,class_teacher.section_id,classes.class,sections.section')->join("staff", "class_teacher.staff_id = staff.id")->join("classes", "class_teacher.class_id = classes.id")->join("sections", "class_teacher.section_id = sections.id")->where("class_teacher.id", $id)->get("class_teacher");

            return $query->row_array();
        } else {

            $query = $this->db->select('staff.*,class_teacher.id as ctid,classes.class,sections.section')->join("staff", "class_teacher.staff_id = staff.id")->join("classes", "class_teacher.class_id = classes.id")->join("sections", "class_teacher.section_id = sections.id")->get("class_teacher");

            return $query->row_array();
        }
    }

    public function addClassTeacher($data) {
		$this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data["id"])) {

            $this->db->where("id", $data["id"])->update("class_teacher", $data);
			$message      = UPDATE_RECORD_CONSTANT." On  class teacher id ".$data["id"];
			$action       = "Update";
			$record_id    = $data["id"];
			$this->log($message, $record_id, $action);
			
			
        } else {

            $this->db->insert("class_teacher", $data);
			$id=$this->db->insert_id();
			$message      = INSERT_RECORD_CONSTANT." On class teacher id ".$id;
			$action       = "Insert";
			$record_id    = $id;
			$this->log($message, $record_id, $action);
			
        }
		//echo $this->db->last_query();die;
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

    function teacherByClassSection($class_id, $section_id) {


        $query = $this->db->select('staff.*,class_teacher.id as ctid,class_teacher.class_id,class_teacher.section_id,classes.class,sections.section')->join("staff", "class_teacher.staff_id = staff.id")->join("classes", "class_teacher.class_id = classes.id")->join("sections", "class_teacher.section_id = sections.id")->where("class_teacher.class_id", $class_id)->where("class_teacher.section_id", $section_id)->get("class_teacher");

        return $query->result_array();
    }

    public function getclassbyuser($id) {

        $query = $this->db->select("classes.*")->join("classes", "class_teacher.class_id = classes.id")->where("class_teacher.staff_id", $id)->get("class_teacher");

        return $query->result_array();
    }

    function classbysubjectteacher($id, $classes) {
      // $query = $this->db->select("classes.*,teacher_subjects.subject_id")->join("class_sections", "class_sections.id = teacher_subjects.class_section_id")->join("classes", "class_sections.class_id = classes.id ")->where("teacher_subjects.teacher_id", $id)->where_not_in("class_sections.class_id", $classes)->group_by("class_sections.class_id")->get("teacher_subjects");

      $query = $this->db->query('SELECT class,id,
        (SELECT TS.subject_id FROM teacher_subjects TS WHERE TS.teacher_id = $id) subject_id,
         (SELECT distinct CS.class_id FROM class_sections CS  WHERE C.ID = CS.class_id and CS.class_id not in $classes) section
          FROM CLASSES C where 1=1'); 
           // $query =  $this->db->select("classes.*,teacher_subjects.subject_id")
           //          ->join("classes", "class_sections.class_id = classes.id ")
           //          ->where("teacher_subjects.teacher_id", $id)
           //          ->where_in('classes.id','select class_sections.class_id from class_sections inner join teacher_subjects on (class_sections.id = teacher_subjects.class_section_id) where class_sections.class_id not in '$classes' group by class_sections.class_id')
           //          ->get("teacher_subjects");

        return $query->result_array();
    }

    public function delete($class_id, $section_id, $array) {
		
        $this->db->where('class_id', $class_id);
        $this->db->where('section_id', $section_id);
        if (!empty($array)) {
            $this->db->where_in('staff_id', $array);
			
        }
        $this->db->delete('class_teacher');
    }

    public function getsubjectbyteacher($id) {

       

          $query =  $this->db->select("classes.*,teacher_subjects.subject_id")
                    ->join("classes", "class_sections.class_id = classes.id ")
                    ->where("teacher_subjects.teacher_id", $id)
                    ->where_in('classes.id','select class_sections.class_id from class_sections inner join teacher_subjects on (class_sections.id = teacher_subjects.class_section_id) group by class_sections.class_id')
                    ->get("teacher_subjects");

                    

        return $query->result_array();
    }

}

?>