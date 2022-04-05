<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Server (ServerController)
 * Server Class to control all server related operations.
 */
class Server extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('server_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Dashboard';
        
        $this->loadViews("dashboard", $this->global, NULL , NULL);
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
            
            $count = $this->server_model->serverListingCount($searchText);

            $returns = $this->paginationCompress("servers/", $count, 10);
            
            $data['serverRecords'] = $this->server_model->serverListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'YouTube Viewer : Server List';
            
            $this->loadViews("server/list", $this->global, $data, NULL);
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
            $this->load->model('server_model');
            // $data['roles'] = $this->server_model->getServerRoles();
            
            $this->global['pageTitle'] = 'YouTube Viewer : Add New Server';

            $this->loadViews("server/add", $this->global, NULL, NULL);
        // }
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewServer()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('server_ip','Server IP','trim|required|max_length[20]');
            $this->form_validation->set_rules('server_provider','Server Provider','required|max_length[128]');
            $this->form_validation->set_rules('maximum_thread','Maximum Thread','required|max_length[128]');
            $this->form_validation->set_rules('end_point','End-Point','required|max_length[128]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $serverIp = $this->input->post('server_ip');
                $serverProvider = $this->input->post('server_provider');
                $maximumThread = $this->input->post('maximum_thread');
                $endPoint = $this->input->post('end_point');

                $lastId = $this->server_model->getLastId();
                
                $serverInfo = array('server_master_id'=>$lastId, 'server_ip'=>$serverIp, 'server_provider'=>$serverProvider,
                'maximum_thread'=>(int)$maximumThread, 'end_point'=>$endPoint, 'created_date'=>date('Y-m-d H:i:s'), 'status'=>'active', 'modified_date'=>date('Y-m-d H:i:s'));
                
                $this->load->model('server_model');
                $result = $this->server_model->addNewServer($serverInfo);
                
                if($result > 0){
                    $this->session->set_flashdata('success', 'Add new server successfully');
                } else {
                    $this->session->set_flashdata('error', 'Add new server failed');
                }
                redirect('server/listing');
            }
        // }
    }

    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($serverMasterId = NULL)
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            if($serverMasterId == null)
            {
                redirect('server/listing');
            }
            
            $data['serverInfo'] = $this->server_model->getServerInfo($serverMasterId);

            $this->global['pageTitle'] = 'YouTube Viewer : Edit Server';
            
            $this->loadViews("server/edit", $this->global, $data, NULL);
        // }
    }
    
    
    /**
     * This function is used to edit the server information
     */
    function editServer()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->library('form_validation');
            
            $serverMasterId = $this->input->post('serverMasterId');
            
            $this->form_validation->set_rules('server_ip','Server IP','trim|required|max_length[20]');
            $this->form_validation->set_rules('server_provider','Server Provider','required|max_length[128]');
            $this->form_validation->set_rules('maximum_thread','Maximum Thread','required|max_length[128]');
            $this->form_validation->set_rules('end_point','End-Point','required|max_length[128]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($serverMasterId);
            }
            else
            {
                $serverIp = $this->input->post('server_ip');
                $serverProvider = $this->input->post('server_provider');
                $maximumThread = $this->input->post('maximum_thread');
                $endPoint = $this->input->post('end_point');
                $status = $this->input->post('status');
                
                $serverInfo = array('server_ip'=>$serverIp, 'server_provider'=>$serverProvider, 'maximum_thread'=>(int)$maximumThread, 'end_point'=>$endPoint, 'status'=>$status, 'modified_date'=>date('Y-m-d H:i:s'));
                
                $result = $this->server_model->editServer($serverInfo, $serverMasterId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Server updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Server updation failed');
                }
                
                redirect('server/listing');
            }
        // }
    }


    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteServer()
    {
        // if(!$this->isAdmin())
        // {
        //     echo(json_encode(array('status'=>'access')));
        // }
        // else
        // {
            $serverMasterId = $this->input->post('serverMasterId');
            // $serverInfo = array('isDeleted'=>1,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
            
            $result = $this->server_model->deleteServer($serverMasterId);
            
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