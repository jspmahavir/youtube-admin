<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Scheduledetail (ScheduleController)
 * Scheduledetail Class to control all server related operations.
 */
class Scheduledetail extends BaseController
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
        $data['schedule_id'] = $this->input->post('schedule_id');
        $this->global['pageTitle'] = 'YouTube Viewer : Schedule Detail';
        $this->loadViews("schedule/detail", $this->global, $data, NULL);
    }

    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function detail()
    {
        $data = $row = array();
        // Fetch schedule detail's records
        $schDetData = $this->schedule_model->getSchDetailRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($schDetData as $schdetail){
            $i++;
            $created = date( 'jS M Y', strtotime($schdetail['created_date']));
            $data[] = array($schdetail['ytvideo_id'], $schdetail['proxy_ip'], $schdetail['proxy_port'], $schdetail['country'], $schdetail['region_name'], $schdetail['city'], $schdetail['zip'], $schdetail['timezone'], $schdetail['isp'], $schdetail['query_ip'], $schdetail['status'], $schdetail['reason'], $created);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->schedule_model->countSchDetailAll($_REQUEST),
            "recordsFiltered" => $this->schedule_model->countSchDetailFiltered($_REQUEST),
            "data" => $data,
        );
        
        // Output to JSON format
        echo json_encode($output);
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