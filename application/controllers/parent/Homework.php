<?php

/**
 * 
 */
class Homework extends Parent_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library("customlib");
        $this->load->model("homework_model");
        $this->load->model("staff_model");
        $this->load->model("student_model");
    }

    public function student_homework($student_id) {

        $this->session->set_userdata('top_menu', 'Homework');
        $this->session->set_userdata('sub_menu', 'parent/homework/student_homework/' . $student_id);
          $student = $this->student_model->get($student_id);
  

        $class_id = $student["class_id"];
        $section_id = $student["section_id"];
        $student_session_id=$student['student_session_id'];
        // $homeworklist = $this->homework_model->getStudentHomework($class_id, $section_id);

        $homeworklist = $this->homework_model->getStudentHomeworkWithStatus($class_id, $section_id,$student_session_id);
        //echo $this->db->last_query();die;
        $data["homeworklist"] = $homeworklist;
        $data["class_id"] = $class_id;
        $data["section_id"] = $section_id;
        $data["student_id"] = $student_id;
        $data["subject_id"] = "";
 //        $userdata = $this->customlib->getLoggedInUserData();

 //        $result = $this->student_model->getRecentRecord($student_id);
 //        $class_id = $result["class_id"];
 //        $section_id = $result["section_id"];
 //        $homeworklist = $this->homework_model->getStudentHomework($class_id, $section_id);
 //        //echo $this->db->last_query();die;
 //        $data["homeworklist"] = $homeworklist;
 //        $data["class_id"] = $class_id;
 //        $data["section_id"] = $section_id;
 //        $data["student_id"] = $student_id;
 //        $data["subject_id"] = "";
 //        if (!empty($homeworklist)) {
 //            foreach ($homeworklist as $key => $value) {

 //                $report = $this->homework_model->getEvaluationReportForStudent($value["id"], $student_id);
 //                $data["homeworklist"][$key]["report"] = $report;
 //            }
 //        }
 // // echo "<pre>";
 // // print_r($data);
 // // echo "<pre>";die;
        $this->load->view("layout/parent/header");
        $this->load->view("parent/homeworklist", $data);
        $this->load->view("layout/parent/footer");
    }

    public function homework_detail($id,$student_id) {

        $data["title"] = "Homework Evaluation";

        $userdata = $this->customlib->getLoggedInUserData();

       
        $result = $this->homework_model->getRecord($id);
        $class_id = $result["class_id"];
        $section_id = $result["section_id"];
        $studentlist = $this->homework_model->getStudents($class_id, $section_id);
        $data["studentlist"] = $studentlist;
        $data["result"] = $result;
        $data["created_by"] = "";
        $data["evaluated_by"] = "";
        $report = $this->homework_model->getEvaluationReportForStudent($id, $student_id);
        $data["report"] = $report;
      
        if (!empty($report)) {
            $create_data = $this->staff_model->get($result["created_by"]);
            $eval_data = $this->staff_model->get($result["evaluated_by"]);
            $created_by = $create_data["name"];
            $evaluated_by = $eval_data["name"];
            $data["created_by"] = $created_by;
            $data["evaluated_by"] = $evaluated_by;
        }
 $data["homeworkdocs"] = $this->homework_model->get_homeworkDocBystudentId($id,$student_id);
    //echo "<pre>"; print_r($data["homeworkdocs"]); echo "<pre>";die;
        $this->load->view("parent/homework_detail", $data);
    }

    public function download($id, $doc) {
        $this->load->helper('download');
        $name = $this->uri->segment(5);
        $ext = explode(".", $name);
        $filepath = "./uploads/homework/" . $id . "." . $ext[1];
        $data = file_get_contents($filepath);
        force_download($name, $data);
    }

    public function get_upload_docs($homework_id){
        $student_id=$_POST['student_id'];
        $data=$this->homework_model->get_upload_docs($arra=array('homework_id'=>$homework_id,'student_id'=>$student_id));
        echo json_encode($data[0]); 
    }
     public function assigmnetDownload($id, $doc)
    {
        $this->load->helper('download');
        $name     = $this->uri->segment(5);
        $ext      = explode(".", $name);
        $filepath = "./uploads/homework/assignment/" . $doc;
        $data     = file_get_contents($filepath);
        force_download($name, $data);
    }

     public function upload_docs()
    {

        $homework_id         = $_REQUEST['homework_id'];
        $student_id          =$_REQUEST['student_id'];
        $data['homework_id'] = $homework_id;
        $data['student_id']  = $student_id;
        $data['message']     = $_REQUEST['message'];
        $data['id']=$_POST['assigment_id'];
        $insert_id           = $this->homework_model->upload_docs($data);

        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $fileInfo     = pathinfo($_FILES["file"]["name"]);
            $data['docs'] = $insert_id . '.' . $fileInfo['extension'];
            move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/homework/assignment/" . $data['docs']);
            $data['id'] = $insert_id;
            $this->homework_model->upload_docs($data);
        }
    }

}

?>