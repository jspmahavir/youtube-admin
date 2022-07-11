<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Dashboard_model (Dashboard Model)
 * Dashboard model class to get to handle dashboard related data 
 * @version : 1.1
 * @since : 15 November 2016
 */
class Dashboard_model extends CI_Model
{
    //custom function by vasudev
    function panelCount()
    {
        $serverCount = $this->mongo_db->get('server_master');
        $count['serverCount'] = count($serverCount);
        $proxyCount = $this->mongo_db->get('proxy_master');
        $count['proxyCount'] = count($proxyCount);
        $scheduleCount = $this->mongo_db->get('schedule_data');
        $count['scheduleCount'] = count($scheduleCount);
        $clientCount = $this->mongo_db->get('api_authentication');
        $count['clientCount'] = count($clientCount);
        return $count;
    }
    
    /**
     * This function is used to update the user information
     * @param array $userInfo : This is users updated information
     * @param number $userId : This is user id
     */
    function editUser($userInfo, $userId)
    {
        $this->mongo_db->where(array('login_id' => (int)$userId));
        $this->mongo_db->set(array('username' => $userInfo['username'], 'modified_date' => $userInfo['modified_date']));
        $this->mongo_db->update('admin_login');
        
        return TRUE;
    }

    /**
     * This function is used to match users password for change password
     * @param number $userId : This is user id
     */
    function matchOldPassword($userId, $oldPassword)
    {
        $this->mongo_db->where('login_id', $userId);
        $user = $this->mongo_db->get('admin_login');

        if(!empty($user)){
            if(verifyHashedPassword($oldPassword, $user[0]['password'])){
                return $user;
            } else {
                return array();
            }
        } else {
            return array();
        }
    }
    
    /**
     * This function is used to change users password
     * @param number $userId : This is user id
     * @param array $userInfo : This is user updation info
     */
    function changePassword($userId, $userInfo)
    {
        $this->mongo_db->where(array('login_id' => (int)$userId));
        $this->mongo_db->set(array('password' => $userInfo['password'], 'modified_date' => $userInfo['modified_date']));
        $this->mongo_db->update('admin_login');
        
        return true;
    }

    /**
     * This function used to get user information by id with role
     * @param number $userId : This is user id
     * @return aray $result : This is user information
     */
    function getUserInfoWithRole($userId)
    {
        $this->mongo_db->where('login_id', $userId);
        $result = $this->mongo_db->get('admin_login');
        
        return $result;
    }

}

  