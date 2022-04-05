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
        redirect('schedule');
    }
    
    /**
     * This function is used to load the server list
     */
    function listing()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {        
            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->schedule_model->scheduleListingCount($searchText);

            $returns = $this->paginationCompress("schedule/", $count, 10);

            $data['scheduleRecords'] = $this->schedule_model->scheduleListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'YouTube Viewer : Schedule List';
            
            $this->loadViews("schedule/list", $this->global, $data, NULL);
        // }
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->model('schedule_model');
            // $data['roles'] = $this->schedule_model->getServerRoles();
            
            $this->global['pageTitle'] = 'YouTube Viewer : Add New Schedule';

            $this->loadViews("schedule/add", $this->global, NULL, NULL);
        // }
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewSchedule()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
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
        // }
    }

    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($scheduleId = NULL)
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            if($scheduleId == null)
            {
                redirect('schedule');
            }
            
            $data['scheduleInfo'] = $this->schedule_model->getScheduleInfo($scheduleId);

            $this->global['pageTitle'] = 'YouTube Viewer : Edit Schedule';
            
            $this->loadViews("schedule/edit", $this->global, $data, NULL);
        // }
    }

    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function detail()
    {
        $scheduleId = $this->input->post('schedule_id');
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            if($scheduleId == null)
            {
                redirect('schedule');
            }
            
            // $data['scheduleInfo'] = $this->schedule_model->getScheduleDetail($scheduleId);

            $searchText = $this->security->xss_clean($this->input->post('searchText'));
            $data['searchText'] = $searchText;
            
            $this->load->library('pagination');
            
            $count = $this->schedule_model->scheduleDetailCount($scheduleId, $searchText);

            $returns = $this->paginationCompress("schedule-detail/", $count, 50);
            
            $data['scheduleDetails'] = $this->schedule_model->getScheduleDetail($scheduleId, $searchText, $returns["page"], $returns["segment"]);

            $data['scheduleId'] = $scheduleId;
            
            $this->global['pageTitle'] = 'YouTube Viewer : Schedule Detail';
            
            $this->loadViews("schedule/detail", $this->global, $data, NULL);
            
            // $this->global['pageTitle'] = 'YouTube Viewer : Edit Schedule';
            
            // $this->loadViews("schedule/detail", $this->global, $data, NULL);
        // }

    }
    

    function commentdetail()
    {
        $scheduleId = $this->input->post('schedule_id');
        if($scheduleId == null)
        {
            redirect('schedule');
        }
        
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;
        
        $this->load->library('pagination');
        
        $count = $this->schedule_model->scheduleCommentDetailCount($scheduleId, $searchText);

        $returns = $this->paginationCompress("schedule-comment-detail/", $count, 50);
        
        $data['scheduleCommentDetails'] = $this->schedule_model->getScheduleCommentDetail($scheduleId, $searchText, $returns["page"], $returns["segment"]);

        $data['scheduleId'] = $scheduleId;
        
        $this->global['pageTitle'] = 'YouTube Viewer : Schedule Comment Detail';
        
        $this->loadViews("schedule/commentdetail", $this->global, $data, NULL);
    }

    function likedetail()
    {
        $scheduleId = $this->input->post('schedule_id');
        if($scheduleId == null)
        {
            redirect('schedule');
        }
        
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;
        
        $this->load->library('pagination');
        
        $count = $this->schedule_model->scheduleLikeDetailCount($scheduleId, $searchText);

        $returns = $this->paginationCompress("schedule-like-detail/", $count, 50);
        
        $data['scheduleLikeDetails'] = $this->schedule_model->getScheduleLikeDetail($scheduleId, $searchText, $returns["page"], $returns["segment"]);

        $data['scheduleId'] = $scheduleId;
        
        $this->global['pageTitle'] = 'YouTube Viewer : Schedule Like Detail';
        
        $this->loadViews("schedule/likedetail", $this->global, $data, NULL);
    }

    function subscribedetail()
    {
        $scheduleId = $this->input->post('schedule_id');
        if($scheduleId == null)
        {
            redirect('schedule');
        }
        
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;
        
        $this->load->library('pagination');
        
        $count = $this->schedule_model->scheduleSubscribeDetailCount($scheduleId, $searchText);

        $returns = $this->paginationCompress("schedule-subscribe-detail/", $count, 50);
        
        $data['scheduleSubscribeDetails'] = $this->schedule_model->getScheduleSubscribeDetail($scheduleId, $searchText, $returns["page"], $returns["segment"]);

        $data['scheduleId'] = $scheduleId;
        
        $this->global['pageTitle'] = 'YouTube Viewer : Schedule Subscribe Detail';
        
        $this->loadViews("schedule/subscribedetail", $this->global, $data, NULL);
    }
    
    /**
     * This function is used to edit the schedule information
     */
    function editSchedule()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
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
        // }
    }


    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteSchedule()
    {
        // if(!$this->isAdmin())
        // {
        //     echo(json_encode(array('status'=>'access')));
        // }
        // else
        // {
            $scheduleMasterId = $this->input->post('scheduleMasterId');
            // $serverInfo = array('isDeleted'=>1,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
            
            $result = $this->schedule_model->deleteSchedule($scheduleMasterId);
            
            if ($result > 0) {
                echo(json_encode(array('status' => TRUE)));
            } else {
                echo(json_encode(array('status' => FALSE)));
            }
        // }
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