<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Gmailauth (GmailauthController)
 * Gmailauth Class to control all app related operations.
 */
class Gmailauth extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('gmail_auth_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the gmail auth
     */
    public function index()
    {
        redirect('gmail-auth');
    }

    function listing() {
        $searchText = $this->security->xss_clean($this->input->post('searchText'));
        $data['searchText'] = $searchText;

        $this->load->library('pagination');
            
        $count = $this->gmail_auth_model->tokenListingCount($searchText);

        $returns = $this->paginationCompress("gmail-auth/", $count, 10);
        
        $data['tokenRecords'] = $this->gmail_auth_model->tokenListing($searchText, $returns["page"], $returns["segment"]);
        
        $this->global['pageTitle'] = 'YouTube Viewer : Token List';
        
        $this->loadViews("gmail-auth/list", $this->global, $data, NULL);
    }

    function add() {
        $this->load->model('gmail_auth_model');
        
        $this->global['pageTitle'] = 'YouTube Viewer : Gmail Authintication';

        $data['appAccount'] = $this->gmail_auth_model->getAppAccount();
        $data['userAccount'] = $this->gmail_auth_model->getUserAccount();

        $this->loadViews("gmail-auth/add", $this->global, $data, NULL);
    }

    function delete()
    {
        $tokenid = $this->input->post('tokenid');
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($tokenid)));
		$result = $this->mongo_db->delete('gmail_auth_token');

        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }
}

?>