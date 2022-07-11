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
        $this->global['pageTitle'] = 'YouTube Viewer : Account List';
        $this->loadViews("account/list", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to load the account list
     */
    function listing()
    {
        $data = $row = array();
        
        // Fetch account's records
        $accData = $this->account_model->getRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($accData as $account){
            $i++;
            $created = date( 'jS M Y', strtotime($account['created_date']));
            $data[] = array($account['email'], $account['password'], $account['recovery_email'], $account['email_validation_pass'], $created, $account['login_id']);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->account_model->countAll(),
            "recordsFiltered" => $this->account_model->countFiltered($_REQUEST),
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
        $this->load->model('account_model');;

        $this->global['pageTitle'] = 'YouTube Viewer : Add New Account';

        $this->loadViews("account/add", $this->global, NULL, NULL);
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
        $this->load->library('form_validation');
        $this->form_validation->set_rules('name','Name','trim|required|max_length[128]');
        $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
        $this->form_validation->set_rules('password','Password','required|max_length[20]');
        $this->form_validation->set_rules('recovery-email','Recovery Email','trim|required|max_length[128]');
        $this->form_validation->set_rules('email-validation-pass','Email Validation Password','required|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->add();
        }
        else
        {
            $name = $this->input->post('name');
            $email = strtolower($this->security->xss_clean($this->input->post('email')));
            $password = $this->input->post('password');
            $recoveryEmail = strtolower($this->security->xss_clean($this->input->post('recovery-email')));
            $emailValidationPass = $this->input->post('email-validation-pass');

            $lastId = $this->account_model->getLastId();
            
            $accountInfo = array('login_id'=>$lastId, 'name'=>$name, 'email'=>$email, 'password'=>$password, 'recovery_email'=>$recoveryEmail, 'email_validation_pass'=>$emailValidationPass, 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
            
            $this->load->model('account_model');
            $result = $this->account_model->addNewAccount($accountInfo);
            
            if($result > 0){
                $this->session->set_flashdata('success', 'Add new account successfully');
            } else {
                $this->session->set_flashdata('error', 'Add new account failed');
            }
            redirect('account');
        }
    }

    
    /**
     * This function is used load account edit information
     * @param number $accountId : Optional : This is account id
     */
    function edit($accountId = NULL)
    {
        if($accountId == null)
        {
            redirect('account');
        }
        
        $data['accountInfo'] = $this->account_model->getAccountInfo($accountId);

        $this->global['pageTitle'] = 'YouTube Viewer : Edit Account';
        
        $this->loadViews("account/edit", $this->global, $data, NULL);
    }
    
    
    /**
     * This function is used to edit the account information
     */
    function editAccount()
    {
        $this->load->library('form_validation');
        
        $accountId = $this->input->post('accountId');

        $this->form_validation->set_rules('name','Name','trim|required|max_length[128]');
        $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
        $this->form_validation->set_rules('password','Password','required|max_length[20]');
        $this->form_validation->set_rules('recovery-email','Recovery Email','trim|required|max_length[128]');
        $this->form_validation->set_rules('email-validation-pass','Email Validation Password','required|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($accountId);
        }
        else
        {
            $name = $this->input->post('name');
            $email = strtolower($this->security->xss_clean($this->input->post('email')));
            $password = $this->input->post('password');
            $recoveryEmail = strtolower($this->security->xss_clean($this->input->post('recovery-email')));
            $emailValidationPass = $this->input->post('email-validation-pass');
            
            $accountInfo = array('name'=>$name, 'email'=>$email, 'password'=>$password, 'recovery_email'=>$recoveryEmail, 'email_validation_pass'=>$emailValidationPass, 'modified_date'=>date('Y-m-d H:i:s'));

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
    }


    /**
     * This function is used to delete the account using accountId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteAccount()
    {
        $accountId = $this->input->post('accountId');
        // $accountInfo = array('isDeleted'=>1,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
        
        $result = $this->account_model->deleteAccount($accountId);
        
        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }

    //import custom function by vasudev
    public function import(){
        $data = array();
        $proxData = array();
        if($this->input->post('importSubmit'))
        {
            $this->form_validation->set_rules('file', 'CSV file', 'callback_file_check');
            if($this->form_validation->run() == true)
            {
                $insertCount = $rowCount = $notAddCount = 0;
                if(is_uploaded_file($_FILES['file']['tmp_name']))
                {
                    $this->load->library('CSVReader');
                    $csvData = $this->csvreader->parse_csv($_FILES['file']['tmp_name']);
                    $lastId = $this->account_model->getLastId();
                    if(!empty($csvData)){
                        foreach($csvData as $row)
                        {
                            $rowCount++;
                            $accountData = array(
                                'login_id' => ($lastId - 1) + $rowCount,
                                'name' => $row['Name'],
                                'email' => $row['Email'],
                                'password' => $row['Password'],
                                'recovery_email' => $row['Recovery Email'],
                                'email_validation_pass' => $row['Email Validation Pass'],
                                'created_date' => date('Y-m-d H:i:s'),
                                'modified_date' => date('Y-m-d H:i:s'),
                            );
                            // Insert account data
                            $insert = $this->account_model->importAccountData($accountData);
                            
                            if($insert){
                                $insertCount++;
                            }
                        }
                        
                        // Status message with imported data count
                        $notAddCount = $rowCount - $insertCount;
                        $successMsg = 'Account imported successfully. Total Rows ('.$rowCount.') | Inserted ('.$insertCount.') | Not Inserted ('.$notAddCount.')';
                        $this->session->set_flashdata('success', $successMsg);
                    }
                } else {
                    $this->session->set_flashdata('error', 'Error on file upload, please try again.');
                }
            } else {
                $this->session->set_flashdata('error', 'Invalid file, please select only CSV file');
            }
        } else {
            $this->session->set_flashdata('error', 'Please select CSV file');
        }
        redirect('account');
    }

    /*
     * Callback function to check file value and type during validation
     */
    public function file_check($str) {
        $allowed_mime_types = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
            $mime = get_mime_by_extension($_FILES['file']['name']);
            $fileAr = explode('.', $_FILES['file']['name']);
            $ext = end($fileAr);
            if(($ext == 'csv') && in_array($mime, $allowed_mime_types)) {
                return true;
            } else {
                $this->form_validation->set_message('file_check', 'Please select only CSV file to upload');
                return false;
            }
        } else {
            $this->form_validation->set_message('file_check', 'Please select a CSV file to upload');
            return false;
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