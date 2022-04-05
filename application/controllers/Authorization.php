
<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';
include_once APPPATH . "/libraries/vendor/autoload.php";

/**
 * Class : Authorization (AuthorizationController)
 * Authorization Class to control all server related operations.
 */
class Authorization extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('authorization_model');
        $this->isLoggedIn();
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $app_id = $this->input->get_post('app');
        $user_email = $this->input->get_post('user');
        $emailpass = explode("-",$user_email);
        $emailId = $emailpass[0];
        if ($app_id && $user_email) {
            $checkData = $this->authorization_model->checkAppData($app_id, $emailId);
            if ($checkData) {
                $this->session->set_flashdata('error', 'Please select other app account or user account');
                $redirect_uri = base_url() . 'gmail-auth';
                redirect($redirect_uri);
            } else {
                $_SESSION["app_id"] = $app_id;
                $_SESSION["user_email"] = $user_email;
            
                $redirect_uri = base_url() . 'oauth2callback';
                redirect($redirect_uri);
            }
        }
    }

    public function saveTokenInfo() {
        $app_id = $this->session->userdata('app_id');
        $user_email = $this->session->userdata('user_email');
        $emailpass = explode("-",$user_email);
        $emailId = $emailpass[0];

        $appData = $this->authorization_model->getClientJson($app_id);
        $appDataArr = json_decode($appData, true);
        $appDataArr = isset($appDataArr['installed']) ? $appDataArr['installed'] : $appDataArr['web'];
        
        $google_client = new Google_Client();
        $google_client->setAccessType("offline");
        $google_client->setClientId($appDataArr['client_id']);
        $google_client->setClientSecret($appDataArr['client_secret']);
        $google_client->addScope("https://www.googleapis.com/auth/youtube.force-ssl");
        // $google_client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $accessTokenData = $this->session->userdata('access_token');

        if (isset($accessTokenData) && $accessTokenData) {
            $auth_token_id = $this->authorization_model->getLastAuthId();
            $accessTokenData['auth_token_id'] = $auth_token_id;
            $accessTokenData['app_id'] = $app_id;
            $accessTokenData['user_email'] = $emailId;
            $accessTokenData['client_id'] = $appDataArr['client_id'];
            $accessTokenData['client_secret'] = $appDataArr['client_secret'];
            $accessTokenData['expire_time'] = date('Y/m/d H:i:s');
            $result = $this->authorization_model->addAuthTokenInfo($accessTokenData);
            $this->session->unset_userdata('access_token');
            $this->session->unset_userdata('app_id');
            $this->session->unset_userdata('user_email');
            unset($accessTokenData);
            
            $this->session->set_flashdata('success', 'Token Data Added successfully..');
            redirect('gmail-auth');
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