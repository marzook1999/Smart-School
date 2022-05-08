<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Homework extends Student_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library("customlib");
        $this->load->model("homework_model");
        $this->load->model("staff_model");
        $this->load->model("student_model");
    }

    public function index()
    {
   



        $this->session->set_userdata('top_menu', 'Homework');

        $student_id            = $this->customlib->getStudentSessionUserID();
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
       
  
        $data["created_by"]   = "";
        $data["evaluated_by"] = "";
        $userdata             = $this->customlib->getLoggedInUserData();
        



        $result = $this->student_model->getRecentRecord($student_id);

        $class_id     = $student_current_class->class_id;
        $section_id   = $student_current_class->section_id;
        $homeworklist = $this->homework_model->getStudentHomeworkWithStatus($class_id, $section_id,$student_current_class->student_session_id);
         $data["homeworklist"] = $homeworklist;
       
       //  echo "<pre/>";
       //  print_r($homeworklist);
       //  exit();

       
       //  foreach ($homeworklist as $key => $value) {
       //      $subject_groups_id=$value['subject_groups_id'];
       //      $subjects[$subject_groups_id]=$this->homework_model->get_HomeworkSubject($subject_groups_id);
       //  }



       // $data['subjects_list']=$subjects;
       //  $data["homeworklist"] = $homeworklist;
       //  $data["class_id"]     = $class_id;
       //  $data["section_id"]   = $section_id;
       //  $data["subject_id"]   = "";

       //  foreach ($homeworklist as $key => $value) {

       //      $report = $this->homework_model->getEvaluationReportForStudent($value["id"], $student_id);

       //      $data["homeworklist"][$key]["report"] = $report;
       //  }

       //  $homework_docs = $this->homework_model->get_homeworkDoc($student_id);
       //  $limit         = count($homework_docs) - 1;
       //  $homework_doc  = array();
       //  for ($i = 0; $i <= $limit; $i++) {
       //      $name                = $homework_docs[$i]['homework_id'];
       //      $homework_doc[$name] = $homework_docs[$i];
       //  }

       //  $data['homework_doc'] = $homework_doc;

        $this->load->view("layout/student/header");
        $this->load->view("user/homework/homeworklist", $data);
        $this->load->view("layout/student/footer");
    }

    public function upload_docs()
    {

        $homework_id         = $_REQUEST['homework_id'];
        $userdata            = $this->customlib->getLoggedInUserData();
        $student_id          = $userdata["student_id"];
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

    public function get_upload_docs($id){
        $userdata             = $this->customlib->getLoggedInUserData();
        $student_id          = $userdata["student_id"];
        $data=$this->homework_model->get_upload_docs($arra=array('homework_id'=>$id,'student_id'=>$student_id));
        echo json_encode($data[0]); 
    }

    public function homework_detail($id)
    {

        $data["title"] = "Homework Evaluation";

        $userdata = $this->customlib->getLoggedInUserData();

        $student_id           = $userdata["student_id"];
        $result               = $this->homework_model->getRecord($id);
       // echo $this->db->last_query();die;
        $class_id             = $result["class_id"];
        $section_id           = $result["section_id"];
        $studentlist          = $this->homework_model->getStudents($class_id, $section_id);
        $data["studentlist"]  = $studentlist;
        $data["result"]       = $result;
        $report               = $this->homework_model->getEvaluationReportForStudent($id, $student_id);
        $data["report"]       = $report;
        $data["created_by"]   = "";
        $data["evaluated_by"] = "";
        $data["homeworkdocs"] = $this->homework_model->get_homeworkDocById($id);

        if (!empty($report)) {
            $create_data          = $this->staff_model->get($result["created_by"]);
            $eval_data            = $this->staff_model->get($result["evaluated_by"]);
            $created_by           = $create_data["name"];
            $evaluated_by         = $eval_data["name"];
            $data["created_by"]   = $created_by;
            $data["evaluated_by"] = $evaluated_by;
        }

        $this->load->view("user/homework/homework_detail", $data);
    }

    public function download($id, $doc)
    {
        $this->load->helper('download');
        $name     = $this->uri->segment(5);
        $ext      = explode(".", $name);
        $filepath = "./uploads/homework/" . $id . "." . $ext[1];
        $data     = file_get_contents($filepath);
        force_download($name, $data);
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

}
