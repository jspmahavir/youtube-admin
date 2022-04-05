<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Oauth2callback (Oauth2callbackController)
 * Oauth2callback Class to control all server related operations.
 */
class Oauth2callback extends BaseController
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
        include_once APPPATH . "/libraries/vendor/autoload.php";
        $app_id = $this->session->userdata('app_id');
        $appData = $this->authorization_model->getClientJson($app_id);
        $appDataArr = json_decode($appData, true);
        $appDataArr = isset($appDataArr['installed']) ? $appDataArr['installed'] : $appDataArr['web'];
        
        $google_client = new Google_Client();
        $google_client->setAccessType("offline");
        $google_client->setClientId($appDataArr['client_id']);
        $google_client->setClientSecret($appDataArr['client_secret']);
        $google_client->addScope("https://www.googleapis.com/auth/youtube.force-ssl");
        // $google_client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
        $google_client->setRedirectUri(base_url() . 'oauth2callback');

        if (!isset($_GET['code'])) {
            $auth_url = $google_client->createAuthUrl();
            redirect($auth_url);
        } else {
            $google_client->authenticate($_GET['code']);
            $_SESSION['access_token'] = $google_client->getAccessToken();
            $redirect_uri = base_url() . 'authorization/saveTokenInfo';
            redirect($redirect_uri);
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