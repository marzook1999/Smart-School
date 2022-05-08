<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Timetable extends Student_Controller
{

    public function __construct()
    {
        parent::__construct();
    }
    public function index()
    {
        $this->session->set_userdata('top_menu', 'Time_table');
         $student_current_class = $this->customlib->getStudentCurrentClsSection();
        
        $student_id               = $this->customlib->getStudentSessionUserID();
        $student                  = $this->student_model->get($student_id);
        $subjet_timetable         = $this->subjectgroup_model->getClassandSectionTimetable($student_current_class->class_id, $student_current_class->section_id);
        $data['subjet_timetable'] = $subjet_timetable;
        $data['result_array'] = $subjet_timetable;
        $this->load->view('layout/student/header', $data);
        $this->load->view('user/timetable/timetableList', $data);
        $this->load->view('layout/student/footer', $data);
    }


}
