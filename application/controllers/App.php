<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : App (AppController)
 * App Class to control all app related operations.
 */
class App extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('app_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the app
     */
    public function index()
    {
        redirect('app');
    }
    
    /**
     * This function is used to load the app list
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
            
            $count = $this->app_model->appListingCount($searchText);

            $returns = $this->paginationCompress("app/", $count, 10);
            
            $data['appRecords'] = $this->app_model->appListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'YouTube Viewer : App List';
            
            $this->loadViews("app/list", $this->global, $data, NULL);
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
            $this->load->model('app_model');
            // $data['roles'] = $this->app_model->getAppRoles();
            
            $this->global['pageTitle'] = 'YouTube Viewer : Add New App';

            $this->loadViews("app/add", $this->global, NULL, NULL);
        // }
    }

    /**
     * This function is used to check whether email already exist or not
     */
    function checkEmailExists()
    {
        $appId = $this->input->post("appId");
        $email = $this->input->post("email");

        if(empty($appId)){
            $result = $this->app_model->checkEmailExists($email);
        } else {
            $result = $this->app_model->checkEmailExists($email, $appId);
        }

        if(empty($result)){ echo("true"); }
        else { echo("false"); }
    }
    
    /**
     * This function is used to add new app to the system
     */
    function addNewApp()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('app_name','App Name','required|max_length[128]');
            $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
            $this->form_validation->set_rules('password','Password','required|max_length[20]');
            $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');
            $this->form_validation->set_rules('client_json','Client JSON','required');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $app_id = $this->app_model->getLastAppId();
                $appname = $this->input->post('app_name');
                $email = strtolower($this->security->xss_clean($this->input->post('email')));
                $password = $this->input->post('password');
                $clientJSON = $this->input->post('client_json');
                
                $appInfo = array('app_id'=>$app_id, 'app_name'=>$appname, 'email'=>$email, 'password'=>$password, 'client_json'=>$clientJSON, 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
                
                $this->load->model('app_model');
                $result = $this->app_model->addNewApp($appInfo);
                
                if($result){
                    $this->session->set_flashdata('success', 'Add new app successfully');
                } else {
                    $this->session->set_flashdata('error', 'Add new app failed');
                }
                redirect('app');
            }
        // }
    }

    
    /**
     * This function is used load app edit information
     * @param number $appId : Optional : This is app id
     */
    function edit($appId = NULL)
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            if($appId == null)
            {
                redirect('app/listing');
            }
            
            $data['appInfo'] = $this->app_model->getAppInfo($appId);

            $this->global['pageTitle'] = 'YouTube Viewer : Edit App';
            
            $this->loadViews("app/edit", $this->global, $data, NULL);
        // }
    }
    
    
    /**
     * This function is used to edit the app information
     */
    function editApp()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->library('form_validation');
            
            $appId = $this->input->post('appId');
            
            $this->form_validation->set_rules('app_name','App Name','required|max_length[128]');
            $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
            $this->form_validation->set_rules('password','Password','required|max_length[20]');
            $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');
            $this->form_validation->set_rules('client_json','Client JSON','required');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($appId);
            }
            else
            {
                $appname = $this->input->post('app_name');
                $email = strtolower($this->security->xss_clean($this->input->post('email')));
                $password = $this->input->post('password');
                $clientJSON = $this->input->post('client_json');

                $appInfo = array();
                
                // if(empty($password))
                // {
                //     $appInfo = array('app_name'=>$appname, 'email'=>$email, 'client_json'=>$clientJSON, 'modified_date'=>date('Y-m-d H:i:s'));
                // }
                // else
                // {
                    $appInfo = array('app_name'=>$appname, 'email'=>$email, 'password'=>$password, 'client_json'=>$clientJSON, 'modified_date'=>date('Y-m-d H:i:s'));
                // }
                
                $result = $this->app_model->editApp($appInfo, $appId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'App updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'App updation failed');
                }
                
                redirect('app');
            }
        // }
    }


    /**
     * This function is used to delete the app using appId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteApp()
    {
        // if(!$this->isAdmin())
        // {
        //     echo(json_encode(array('status'=>'access')));
        // }
        // else
        // {
            $appId = $this->input->post('appId');
            // $appInfo = array('isDeleted'=>1,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
            
            $result = $this->app_model->deleteApp($appId);
            
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