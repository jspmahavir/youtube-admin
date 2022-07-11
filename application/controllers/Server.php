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
        $this->global['pageTitle'] = 'YouTube Viewer : Server List';
        
        $this->loadViews("server/list", $this->global, NULL , NULL);
    }
    
    /**
     * This function is used to load the server list
     */
    function listing()
    {
        $data = $row = array();
        
        // Fetch server's records
        $serData = $this->server_model->getRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($serData as $server){
            $i++;
            $created = date( 'jS M Y', strtotime($server['created_date']));
            $data[] = array($server['server_ip'], $server['server_provider'], $server['maximum_thread'], $server['end_point'], $server['status'], $created, $server['server_master_id']);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->server_model->countAll(),
            "recordsFiltered" => $this->server_model->countFiltered($_REQUEST),
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
        $this->load->model('server_model');

        $this->global['pageTitle'] = 'YouTube Viewer : Add New Server';

        $this->loadViews("server/add", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewServer()
    {
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
            redirect('server');
        }
    }

    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($serverMasterId = NULL)
    {
        if($serverMasterId == null)
        {
            redirect('server');
        }

        $data['serverInfo'] = $this->server_model->getServerInfo($serverMasterId);

        $this->global['pageTitle'] = 'YouTube Viewer : Edit Server';
        
        $this->loadViews("server/edit", $this->global, $data, NULL);
    }
    
    
    /**
     * This function is used to edit the server information
     */
    function editServer()
    {
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
            
            redirect('server');
        }
    }


    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteServer()
    {
        $serverMasterId = $this->input->post('serverMasterId');

        $result = $this->server_model->deleteServer($serverMasterId);

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