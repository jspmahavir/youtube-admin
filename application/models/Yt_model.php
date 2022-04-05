<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Yt_model (Yt Model)
 * Yt model class to get to handle
 */
class Yt_model extends CI_Model
{
    /**
     * This function used to get account information by id
     * @param number $id : This is auto id from Mongo
     * @return array $result : This is token information
     */
    function getGmailAuthTokenData($tid)
    {
        $this->mongo_db->where('_id', new MongoDB\BSON\ObjectID($tid));
        $tokenData = $this->mongo_db->get('gmail_auth_token');
        if($tokenData) {
            return $tokenData[0];
        }
    }

}