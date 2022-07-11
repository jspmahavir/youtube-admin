<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Client (ClientController)
 * Client Class to control all server related operations.
 */
class Client extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('client_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Client List';
        $this->loadViews("client/list", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to load the server list
     */
    function listing()
    {
        $data = $row = array();
        
        // Fetch client's records
        $accData = $this->client_model->getRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($accData as $client){
            $i++;
            $created = date( 'jS M Y', strtotime($client['created_date']));
            $data[] = array($client['client_name'], $client['api_key'], $client['whitelisted_server_ip'], $client['ytview_support'], $client['ytcomment_support'], $client['ytlike_support'], $client['ytsubscribe_support'], $created, $client['authentication_id']);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->client_model->countAll(),
            "recordsFiltered" => $this->client_model->countFiltered($_REQUEST),
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
        $this->load->model('client_model');
        
        $this->global['pageTitle'] = 'YouTube Viewer : Add New Client';

        $this->loadViews("client/add", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewClient()
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('client_name','Client Name','required|max_length[128]');
        $this->form_validation->set_rules('api_key','API Key','trim|required|max_length[128]');
        $this->form_validation->set_rules('white_listed_ip','White Listed IP','required|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->add();
        }
        else
        {
            $clientName = $this->input->post('client_name');
            $apiKey = $this->security->xss_clean($this->input->post('api_key'));
            $whiteListedIP = $this->security->xss_clean($this->input->post('white_listed_ip'));
            $viewSupport = $this->input->post('view_support') ? 1 : 0;
            $commentSupport = $this->input->post('comment_support') ? 1 : 0;
            $likeSupport = $this->input->post('like_support') ? 1 : 0;
            $subscribeSupport = $this->input->post('subscribe_support') ? 1 : 0;

            $lastId = $this->client_model->getLastId();
            
            $clientInfo = array('authentication_id'=>$lastId, 'client_name'=>$clientName, 'api_key'=>$apiKey, 'whitelisted_server_ip'=>$whiteListedIP, 'ytview_support'=>$viewSupport, 'ytcomment_support'=>$commentSupport, 'ytlike_support'=>$likeSupport, 'ytsubscribe_support'=>$subscribeSupport, 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
            
            $this->load->model('client_model');
            $result = $this->client_model->addNewClient($clientInfo);
            
            if($result > 0){
                $this->session->set_flashdata('success', 'Add new client successfully');
            } else {
                $this->session->set_flashdata('error', 'Add new client failed');
            }
            redirect('client');
        }
    }
    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($clientId = NULL)
    {
        if($clientId == null)
        {
            redirect('client');
        }
        
        $data['clientInfo'] = $this->client_model->getClientInfo($clientId);

        $this->global['pageTitle'] = 'YouTube Viewer : Edit Client';
        
        $this->loadViews("client/edit", $this->global, $data, NULL);
    }
    
    
    /**
     * This function is used to edit the client information
     */
    function editClient()
    {
        $this->load->library('form_validation');

        $clientId = $this->input->post('clientId');
        
        $this->form_validation->set_rules('client_name','Client Name','required|max_length[128]');
        $this->form_validation->set_rules('api_key','API Key','trim|required|max_length[128]');
        $this->form_validation->set_rules('white_listed_ip','White Listed IP','required|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($clientId);
        }
        else
        {
            $clientName = $this->input->post('client_name');
            $apiKey = $this->security->xss_clean($this->input->post('api_key'));
            $whiteListedIP = $this->security->xss_clean($this->input->post('white_listed_ip'));
            $viewSupport = $this->input->post('view_support') ? 1 : 0;
            $commentSupport = $this->input->post('comment_support') ? 1 : 0;
            $likeSupport = $this->input->post('like_support') ? 1 : 0;
            $subscribeSupport = $this->input->post('subscribe_support') ? 1 : 0;

            $clientInfo = array('client_name'=>$clientName, 'api_key'=>$apiKey, 'whitelisted_server_ip'=>$whiteListedIP, 'ytview_support'=>$viewSupport, 'ytcomment_support'=>$commentSupport, 'ytlike_support'=>$likeSupport, 'ytsubscribe_support'=>$subscribeSupport, 'modified_date'=>date('Y-m-d H:i:s'));
            
            $result = $this->client_model->editClient($clientInfo, $clientId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'Client updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Client updation failed');
            }
            
            redirect('client');
        }
    }

    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteClient()
    {
        $clientId = $this->input->post('clientId');
        
        $result = $this->client_model->deleteClient($clientId);
        
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