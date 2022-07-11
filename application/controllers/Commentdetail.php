<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Commentdetail (CommentController)
 * Comment Class to control all server related operations.
 */
class Commentdetail extends BaseController
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
        $this->global['pageTitle'] = 'YouTube Viewer : Comment Detail';
        $this->loadViews("schedule/commentdetail", $this->global, $data, NULL);
    }

    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function detail()
    {
        $data = $row = array();
        // Fetch schedule detail's records
        $cmtDetData = $this->schedule_model->getCmtDetailRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($cmtDetData as $cmtdetail){
            $i++;
            $created = date( 'jS M Y', strtotime($cmtdetail['created']));
            $data[] = array($cmtdetail['video_id'], $cmtdetail['comment'], $cmtdetail['user_email'], $cmtdetail['status'], $cmtdetail['error_msg'], $created);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->schedule_model->countCmtDetailAll($_REQUEST),
            "recordsFiltered" => $this->schedule_model->countCmtDetailFiltered($_REQUEST),
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