<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Yt (YtController)
 * Yt Class to control all server related operations.
 */
class Yt extends BaseController
{
    /**
     * This is default constructor of the class
     */
    protected $accessToken;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('yt_model');
        // $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $gmailAuthTokenId = "61fa1e37733f4804c64632c2";
        $tokenInfo = $this->yt_model->getGmailAuthTokenData($gmailAuthTokenId);
        if($tokenInfo) {
            $accessTokenData = $this->generateAccessToken($tokenInfo);
            $accessTokenData = json_decode($accessTokenData, true);
            $this->accessToken = $accessTokenData['access_token'];
            $this->ytLike($tokenInfo);
            // $this->ytSubscription($tokenInfo);
            // $this->ytAddComment($tokenInfo);
        }
    }

    public function generateAccessToken($info) {
        $postArr = array('client_id' => $info['client_id'], 'refresh_token' => $info['refresh_token'], 'client_secret' => $info['client_secret'], 'grant_type' => 'refresh_token');
        $token_url = $this->config->item('yt_token_url');
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postArr));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function ytLike($info) {
        $yt_video_id = 'OQij6GB2FA8';
        $curl_url = $this->config->item('yt_like_url');
        $curl_url = $curl_url."?id=".$yt_video_id."&rating=like";

        $httpHeader[] = "Authorization: Bearer ". $this->accessToken;
        $httpHeader[] = "Content-Length: 0";
        
        $ch = curl_init($curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        $response = curl_exec($ch);

        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }


    public function ytSubscription($info) {
        $curl_url = $this->config->item('yt_subscribe_url');
        $curl_url = $curl_url."?part=snippet";
        $channelId = "UCxVRDu9ujwOrmDxu72V3ujQ";
        $postData = array("snippet" => array("resourceId" => array("channelId" => $channelId)));

        $httpHeader[] = "Authorization: Bearer ". $this->accessToken;
        $httpHeader[] = "Content-Type: application/json";
        
        $ch = curl_init($curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        $response = curl_exec($ch);

        echo "<pre>";
        print_r($response);
        echo "</pre>";
    }
    
    public function ytAddComment($info) {
        $curl_url = $this->config->item('yt_comment_url');
        $curl_url = $curl_url."?part=snippet";
        $videoId = "uQchPj8T5M8";
        $textOriginal = "Nice Activity..";

        $postData = array("snippet" => array("topLevelComment" => array("snippet" => array("videoId" => $videoId, "textOriginal" => $textOriginal))));

        $httpHeader[] = "Authorization: Bearer ". $this->accessToken;
        $httpHeader[] = "Content-Type: application/json";
        
        $ch = curl_init($curl_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
        $response = curl_exec($ch);

        echo "<pre>";
        print_r($response);
        echo "</pre>";
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