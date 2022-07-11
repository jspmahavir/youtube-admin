<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Schedule (ScheduleController)
 * Schedule Class to control all server related operations.
 */
class Schedule extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('schedule_model');
        $this->isLoggedIn();
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Schedule List';
        $this->loadViews("schedule/list", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to load the server list
     */
    function listing()
    {
        $data = $row = array();
        
        // Fetch account's records
        $scheData = $this->schedule_model->getRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($scheData as $schedule){
            $i++;
            $created = date( 'jS M Y H:i:s', strtotime($schedule['created_date']));
            $data[] = array($schedule['video_url'], $schedule['channelId'], $schedule['video_duration'], $schedule['scheduled_view_count'], $schedule['completed_view_count'], $schedule['scheduled_like_count'], $schedule['completed_like_count'], $schedule['scheduled_comment_count'], $schedule['completed_comment_count'],$schedule['scheduled_subscribe_count'], $schedule['completed_subscribe_count'], $schedule['keyword'], $created, $schedule['status'], $schedule['schedule_data_id']);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->schedule_model->countAll(),
            "recordsFiltered" => $this->schedule_model->countFiltered($_REQUEST),
            "data" => $data,
        );
        
        // Output to JSON format
        echo json_encode($output);
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        $this->load->model('schedule_model');
        
        $this->global['pageTitle'] = 'YouTube Viewer : Add New Schedule';

        $this->loadViews("schedule/add", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewSchedule()
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('schedule_url','Schedule URL','trim|required|max_length[128]');
        // $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
        $this->form_validation->set_rules('schedule_port','Schedule Port','required|min_length[5]');
        $this->form_validation->set_rules('username','Username','trim|required|max_length[128]');
        $this->form_validation->set_rules('password','Password','required|max_length[20]');
        $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');
        // $this->form_validation->set_rules('role','Role','trim|required|numeric');
        // $this->form_validation->set_rules('mobile','Mobile Number','required|min_length[10]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->add();
        }
        else
        {
            // $email = strtolower($this->security->xss_clean($this->input->post('email')));
            $scheduleUrl = $this->input->post('schedule_url');
            $schedulePort = $this->security->xss_clean($this->input->post('schedule_port'));
            $username = ucwords(strtolower($this->security->xss_clean($this->input->post('username'))));
            $password = $this->input->post('password');
            // $mobile = $this->security->xss_clean($this->input->post('mobile'));
            // $isAdmin = $this->input->post('isAdmin');
            $lastId = $this->schedule_model->getLastId();
            
            $scheduleInfo = array('schedule_master_id'=>$lastId, 'schedule_url'=>$scheduleUrl, 'schedule_port'=>(int)$schedulePort, 'username'=>$username, 'password'=>getHashedPassword($password), 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
            
            $this->load->model('schedule_model');
            $result = $this->schedule_model->addNewSchedule($scheduleInfo);
            
            if($result > 0){
                $this->session->set_flashdata('success', 'Add new schedule successfully');
            } else {
                $this->session->set_flashdata('error', 'Add new schedule failed');
            }
            redirect('schedule');
        }
    }

    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($scheduleId = NULL)
    {
        if($scheduleId == null)
        {
            redirect('schedule');
        }
        
        $data['scheduleInfo'] = $this->schedule_model->getScheduleInfo($scheduleId);

        $this->global['pageTitle'] = 'YouTube Viewer : Edit Schedule';
        
        $this->loadViews("schedule/edit", $this->global, $data, NULL);
    }
    
    /**
     * This function is used to edit the schedule information
     */
    function editSchedule()
    {
        $this->load->library('form_validation');

        $scheduleMasterId = $this->input->post('scheduleMasterId');
        
        $this->form_validation->set_rules('schedule_url','Schedule URL','trim|required|max_length[128]');
        // $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
        $this->form_validation->set_rules('schedule_port','Schedule Port','required|min_length[5]');
        $this->form_validation->set_rules('username','Username','trim|required|max_length[128]');
        $this->form_validation->set_rules('password','Password','matches[cpassword]|max_length[20]');
        $this->form_validation->set_rules('cpassword','Confirm Password','matches[password]|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($scheduleMasterId);
        }
        else
        {
            $scheduleUrl = $this->input->post('schedule_url');
            $schedulePort = $this->security->xss_clean($this->input->post('schedule_port'));
            $username = ucwords(strtolower($this->security->xss_clean($this->input->post('username'))));
            $password = $this->input->post('password');

            $scheduleInfo = array();
            
            if(empty($password))
            {
                $scheduleInfo = array('schedule_url'=>$scheduleUrl, 'schedule_port'=>(int)$schedulePort, 'username'=>$username, 'modified_date'=>date('Y-m-d H:i:s'));
            }
            else
            {
                $scheduleInfo = array('schedule_url'=>$scheduleUrl, 'schedule_port'=>(int)$schedulePort, 'username'=>$username, 'password'=>getHashedPassword($password), 'modified_date'=>date('Y-m-d H:i:s'));
            }
            
            $result = $this->schedule_model->editSchedule($scheduleInfo, $scheduleMasterId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'Schedule updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Schedule updation failed');
            }
            
            redirect('schedule');
        }
    }


    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteSchedule()
    {
        $scheduleMasterId = $this->input->post('scheduleMasterId');
        
        $result = $this->schedule_model->deleteSchedule($scheduleMasterId);
        
        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }

    function updateSchedule() {
        $scheduleMasterId = $this->input->post('scheduleMasterId');
        $scheduleStatusId = $this->input->post('scheduleStatusId');
        $result = $this->schedule_model->updateSchedule($scheduleMasterId, $scheduleStatusId);

        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }

    /**
     * Page not found : error 404
     */
    function pageNotFound()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : 404 - Page Not Found';
        
        $this->loadViews("404", $this->global, NULL, NULL);
    }
}

?>