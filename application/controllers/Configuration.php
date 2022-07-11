<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Configuration (ConfigurationController)
 * Configuration Class to control all configuration related operations.
 */
class Configuration extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('configuration_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the configuration
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Configuration';
        $data['configData'] = $this->configuration_model->configList();
        
        $this->loadViews("configuration", $this->global, $data , NULL);
    }
    
    /**
     * This function is used to load the configuration list
     */
    function listing()
    {
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;
        
        $this->load->library('pagination');
        
        $count = $this->configuration_model->configurationListingCount($searchText);

        $returns = $this->paginationCompress("configuration/", $count, 10);
        
        $data['configurationRecords'] = $this->configuration_model->configurationListing($searchText, $returns["page"], $returns["segment"]);
        
        $this->global['pageTitle'] = 'YouTube Viewer : Configuration List';
        
        $this->loadViews("configuration/list", $this->global, $data, NULL);
    }

    /**
     * This function is used to load the add new form
     */
    function edit()
    {
        $this->load->model('configuration_model');

        $data['configurationInfo'] = $this->configuration_model->getConfigurationInfo();
        
        $this->global['pageTitle'] = 'YouTube Viewer : Configuration';

        $this->loadViews("configuration/edit", $this->global, $data, NULL);
    }
    
    /**
     * This function is used to add new configuration to the system
     */
    function editConfiguration()
    {
        $configId = $this->input->post('configId');
        $labelArr = $this->input->post('label');
        $configArr = $this->input->post('config');
        $dataConfig = Array();
        if(!empty($labelArr) && !empty($configArr)){
            for($i = 0; $i < count($labelArr); $i++){
                if(!empty($labelArr[$i])){
                    $dataConfig[$labelArr[$i]] = $configArr[$i];
                }
            }
            $toJSON = json_encode($dataConfig);

            $configurationInfo = array('id'=>$configId, 'configuration'=>$toJSON, 'modified_date'=>date('Y-m-d H:i:s'));
            $this->load->model('configuration_model');
            $result = $this->configuration_model->editConfiguration($configurationInfo);

            if($result > 0){
                $this->session->set_flashdata('success', 'Successfully update configuration');
            } else {
                $this->session->set_flashdata('error', 'Configuration update failed');
            }
            redirect('configuration');
        } else {
            $this->session->set_flashdata('error', 'Please fill configuration value');
            $this->edit();
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