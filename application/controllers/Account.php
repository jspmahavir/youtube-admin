<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Account (AccountController)
 * Account Class to control all account related operations.
 */
class Account extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('account_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the account
     */
    public function index()
    {
        redirect('account');
    }
    
    /**
     * This function is used to load the account list
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
            
            $count = $this->account_model->accountListingCount($searchText);

            $returns = $this->paginationCompress("account/", $count, 10);
            
            $data['accountRecords'] = $this->account_model->accountListing($searchText, $returns["page"], $returns["segment"]);
            
            $this->global['pageTitle'] = 'YouTube Viewer : Account List';
            
            $this->loadViews("account/list", $this->global, $data, NULL);
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
            $this->load->model('account_model');
            // $data['roles'] = $this->account_model->getAccountRoles();
            
            $this->global['pageTitle'] = 'YouTube Viewer : Add New Account';

            $this->loadViews("account/add", $this->global, NULL, NULL);
        // }
    }

    /**
     * This function is used to check whether email already exist or not
     */
    function checkEmailExists()
    {
        $accountId = $this->input->post("accountId");
        $email = $this->input->post("email");

        if(empty($accountId)){
            $result = $this->account_model->checkEmailExists($email);
        } else {
            $result = $this->account_model->checkEmailExists($email, $accountId);
        }

        if(empty($result)){ echo("true"); }
        else { echo("false"); }
    }
    
    /**
     * This function is used to add new account to the system
     */
    function addNewAccount()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
            $this->form_validation->set_rules('password','Password','required|max_length[20]');
            $this->form_validation->set_rules('cpassword','Confirm Password','trim|required|matches[password]|max_length[20]');
            $this->form_validation->set_rules('last_login_ip','Last Login IP','required|max_length[128]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $email = strtolower($this->security->xss_clean($this->input->post('email')));
                $password = $this->input->post('password');
                $lastLoginIP = $this->input->post('last_login_ip');

                $lastId = $this->account_model->getLastId();
                
                $accountInfo = array('login_id'=>$lastId, 'email'=>$email, 'password'=>$password, 'last_login_ip'=>$lastLoginIP, 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
                
                $this->load->model('account_model');
                $result = $this->account_model->addNewAccount($accountInfo);
                
                if($result > 0){
                    $this->session->set_flashdata('success', 'Add new account successfully');
                } else {
                    $this->session->set_flashdata('error', 'Add new account failed');
                }
                redirect('account');
            }
        // }
    }

    
    /**
     * This function is used load account edit information
     * @param number $accountId : Optional : This is account id
     */
    function edit($accountId = NULL)
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            if($accountId == null)
            {
                redirect('account');
            }
            
            $data['accountInfo'] = $this->account_model->getAccountInfo($accountId);

            $this->global['pageTitle'] = 'YouTube Viewer : Edit Account';
            
            $this->loadViews("account/edit", $this->global, $data, NULL);
        // }
    }
    
    
    /**
     * This function is used to edit the account information
     */
    function editAccount()
    {
        // if(!$this->isAdmin())
        // {
        //     $this->loadThis();
        // }
        // else
        // {
            $this->load->library('form_validation');
            
            $accountId = $this->input->post('accountId');
            
            $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
            $this->form_validation->set_rules('password','Password','matches[cpassword]|max_length[20]');
            $this->form_validation->set_rules('cpassword','Confirm Password','matches[password]|max_length[20]');
            $this->form_validation->set_rules('last_login_ip','Last Login IP','required|max_length[128]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($accountId);
            }
            else
            {
                $email = strtolower($this->security->xss_clean($this->input->post('email')));
                $password = $this->input->post('password');
                $lastLoginIP = $this->input->post('last_login_ip');
                
                $accountInfo = array('email'=>$email, 'password'=>$password, 'last_login_ip'=>$lastLoginIP, 'modified_date'=>date('Y-m-d H:i:s'));
                
                $result = $this->account_model->editAccount($accountInfo, $accountId);
                
                if($result == true)
                {
                    $this->session->set_flashdata('success', 'Account updated successfully');
                }
                else
                {
                    $this->session->set_flashdata('error', 'Account updation failed');
                }
                
                redirect('account');
            }
        // }
    }


    /**
     * This function is used to delete the account using accountId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteAccount()
    {
        // if(!$this->isAdmin())
        // {
        //     echo(json_encode(array('status'=>'access')));
        // }
        // else
        // {
            $accountId = $this->input->post('accountId');
            // $accountInfo = array('isDeleted'=>1,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
            
            $result = $this->account_model->deleteAccount($accountId);
            
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