<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Authorization_model (Authorization Model)
 * Authorization model class to get to handle authorization related data
 */
class Authorization_model extends CI_Model
{
    /**
     * This function is used to add access token to system
     * @return number $insert_id : This is last inserted id
     */
    function addAuthTokenInfo($tokenInfo)
    {
        $inserted_data = $this->mongo_db->insert('gmail_auth_token', $tokenInfo);
        return $inserted_data;

        // $insert_id = $this->mongo_db->get_where('gmail_accounts', array('login_id' => $inserted_data['login_id']));

        // if ($insert_id) {
        //     return $insert_id[0]['login_id'];
        // } else {
        //     return false;
        // }
    }

    /**
     * This function used to get account information by id
     * @param number $accountId : This is account id
     * @return array $result : This is account information
     */
    function getClientJson($appId)
    {
        $this->mongo_db->where('_id', new MongoDB\BSON\ObjectID($appId));
        $appData = $this->mongo_db->get('app_management');
        return $appData[0]['client_json'];
    }
    function checkAppData($appId, $userEmail)
    {
        $this->mongo_db->where(array('app_id' => $appId, 'user_email' => $userEmail));
        $authEntry = $this->mongo_db->get('gmail_auth_token');
        if ($authEntry) {
            return true;
        } else {
            return false;
        }
    }
    public function getLastAuthId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('gmail_auth_token');
        if ($last_id) {
            return $last_id[0]['auth_token_id'] + 1;
        } else {
            return 1;
        }
    }
}