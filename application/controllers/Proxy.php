<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Proxy (ProxyController)
 * Proxy Class to control all server related operations.
 */
class Proxy extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('proxy_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Proxy List';
        $this->loadViews("proxy/list", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to load the server list
     */
    function listing()
    {
        $data = $row = array();
        
        // Fetch proxy's records
        $proData = $this->proxy_model->getRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($proData as $proxy){
            $i++;
            $created = date( 'jS M Y', strtotime($proxy['created_date']));
            $data[] = array($proxy['proxy_master_id'], $proxy['proxy_url'], $proxy['proxy_port'], $proxy['username'], $created, $proxy['proxy_master_id']);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->proxy_model->countAll(),
            "recordsFiltered" => $this->proxy_model->countFiltered($_REQUEST),
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
        $this->load->model('proxy_model');

        $this->global['pageTitle'] = 'YouTube Viewer : Add New Proxy';

        $this->loadViews("proxy/add", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewProxy()
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('proxy_url','Proxy URL','trim|required|max_length[128]');
        // $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
        $this->form_validation->set_rules('proxy_port','Proxy Port','required|min_length[5]');
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
            $proxyUrl = $this->input->post('proxy_url');
            $proxyPort = $this->security->xss_clean($this->input->post('proxy_port'));
            $username = ucwords(strtolower($this->security->xss_clean($this->input->post('username'))));
            $password = $this->input->post('password');
            // $mobile = $this->security->xss_clean($this->input->post('mobile'));
            // $isAdmin = $this->input->post('isAdmin');
            $lastId = $this->proxy_model->getLastId();
            
            $proxyInfo = array('proxy_master_id'=>$lastId, 'proxy_url'=>$proxyUrl, 'proxy_port'=>(int)$proxyPort, 'username'=>$username, 'password'=>getHashedPassword($password), 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
            
            $this->load->model('proxy_model');
            $result = $this->proxy_model->addNewProxy($proxyInfo);
            
            if($result > 0){
                $this->session->set_flashdata('success', 'Add new proxy successfully');
            } else {
                $this->session->set_flashdata('error', 'Add new proxy failed');
            }
            redirect('proxy');
        }
    }

    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($proxyMasterId = NULL)
    {
        if($proxyMasterId == null)
        {
            redirect('proxy');
        }

        $data['proxyInfo'] = $this->proxy_model->getProxyInfo($proxyMasterId);

        $this->global['pageTitle'] = 'YouTube Viewer : Edit Proxy';

        $this->loadViews("proxy/edit", $this->global, $data, NULL);
    }
    
    /**
     * This function is used to edit the proxy information
     */
    function editProxy()
    {
        $this->load->library('form_validation');

        $proxyMasterId = $this->input->post('proxyMasterId');
        
        $this->form_validation->set_rules('proxy_url','Proxy URL','trim|required|max_length[128]');
        // $this->form_validation->set_rules('email','Email','trim|required|valid_email|max_length[128]');
        $this->form_validation->set_rules('proxy_port','Proxy Port','required|min_length[5]');
        $this->form_validation->set_rules('username','Username','trim|required|max_length[128]');
        $this->form_validation->set_rules('password','Password','matches[cpassword]|max_length[20]');
        $this->form_validation->set_rules('cpassword','Confirm Password','matches[password]|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($proxyMasterId);
        }
        else
        {
            $proxyUrl = $this->input->post('proxy_url');
            $proxyPort = $this->security->xss_clean($this->input->post('proxy_port'));
            $username = ucwords(strtolower($this->security->xss_clean($this->input->post('username'))));
            $password = $this->input->post('password');

            $proxyInfo = array();
            
            if(empty($password))
            {
                $proxyInfo = array('proxy_url'=>$proxyUrl, 'proxy_port'=>(int)$proxyPort, 'username'=>$username, 'modified_date'=>date('Y-m-d H:i:s'));
            }
            else
            {
                $proxyInfo = array('proxy_url'=>$proxyUrl, 'proxy_port'=>(int)$proxyPort, 'username'=>$username, 'password'=>getHashedPassword($password), 'modified_date'=>date('Y-m-d H:i:s'));
            }
            
            $result = $this->proxy_model->editProxy($proxyInfo, $proxyMasterId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'Proxy updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Proxy updation failed');
            }
            
            redirect('proxy');
        }
    }


    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteProxy()
    {
        $proxyMasterId = $this->input->post('proxyMasterId');
        // $serverInfo = array('isDeleted'=>1,'updatedBy'=>$this->vendorId, 'updatedDtm'=>date('Y-m-d H:i:s'));
        
        $result = $this->proxy_model->deleteProxy($proxyMasterId);
        
        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }

    /**
     * This function is used to delete the all proxy
     * @return boolean $result : TRUE / FALSE
     */
    function deleteAll()
    {
        $result = $this->proxy_model->deleteAll();
        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }

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
                    $lastId = $this->proxy_model->getLastId();
                    if(!empty($csvData)){
                        foreach($csvData as $row)
                        {
                            $rowCount++;
                            $proxData = array(
                                'proxy_master_id' => ($lastId - 1) + $rowCount,
                                'proxy_url' => $row['ProxyURL'],
                                'proxy_port' => $row['ProxyPort'],
                                'username' => $row['Username'],
                                'password' => $row['Password'],
                                'created_date' => date('Y-m-d H:i:s'),
                                'modified_date' => date('Y-m-d H:i:s'),
                            );
                            // Insert proxy data
                            $insert = $this->proxy_model->importProxyData($proxData);
                            
                            if($insert){
                                $insertCount++;
                            }
                        }
                        
                        // Status message with imported data count
                        $notAddCount = $rowCount - $insertCount;
                        $successMsg = 'Proxy imported successfully. Total Rows ('.$rowCount.') | Inserted ('.$insertCount.') | Not Inserted ('.$notAddCount.')';
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
        redirect('proxy');
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