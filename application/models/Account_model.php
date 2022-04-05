<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Account_model (Account Model)
 * Account model class to get to handle account related data
 */
class Account_model extends CI_Model
{
    /**
     * This function is used to get the account listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function accountListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->where('account_provider', 'regexp', '/^'.$searchText.'/i');
        }
        $result = $this->mongo_db->get('gmail_accounts');

        // echo "<pre>";
        // print_r($result);

        // $this->db->select('BaseTbl.accountId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_accounts as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // // $this->db->where('BaseTbl.roleId !=', 1);
        // $query = $this->db->get();
        
        return count($result);
    }
    
    /**
     * This function is used to get the account listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function accountListing($searchText = '', $page, $segment)
    {
        $this->mongo_db->order_by(array('login_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('gmail_accounts');

        // $this->db->select('BaseTbl.accountId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_accounts as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        // $this->db->order_by('BaseTbl.accountId', 'DESC');
        // $this->db->limit($page, $segment);
        // $query = $this->db->get();
        
        // $result = $query->result();
        return $result;
    }

    /**
     * This function is used to check whether email id is already exist or not
     * @param {string} $email : This is email id
     * @param {number} $appId : This is app id
     * @return {mixed} $result : This is searched result
     */
    function checkEmailExists($email, $accountId = 0)
    {
        if($accountId != 0){
            $this->mongo_db->where_ne('login_id', (int)$accountId);
            $this->mongo_db->where(array('email' => $email));
            $emailExist = $this->mongo_db->get('gmail_accounts');
        } else {
            $emailExist = $this->mongo_db->get_where('gmail_accounts', array('email' => $email));
        }
        return $emailExist;
    }
    
    /**
     * This function is used to add new account to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewAccount($accountInfo)
    {
        $inserted_data = $this->mongo_db->insert('gmail_accounts', $accountInfo);
        $insert_id = $this->mongo_db->get_where('gmail_accounts', array('login_id' => $inserted_data['login_id']));
        if ($insert_id) {
            return $insert_id[0]['login_id'];
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get account information by id
     * @param number $accountId : This is account id
     * @return array $result : This is account information
     */
    function getAccountInfo($accountId)
    {
        $this->mongo_db->where(array('login_id' => (int)$accountId));
        $accountData = $this->mongo_db->get('gmail_accounts');
        return $accountData;
    }
    
    
    /**
     * This function is used to update the account information
     * @param array $accountInfo : This is accounts updated information
     * @param number $accountId : This is account id
     */
    function editAccount($accountInfo, $accountId)
    {
        $this->mongo_db->where(array('login_id' => (int)$accountId));
        $this->mongo_db->set(array('email' => $accountInfo['email'], 'password' => $accountInfo['password'], 'last_login_ip' => $accountInfo['last_login_ip'], 'modified_date' => $accountInfo['modified_date']));
        $this->mongo_db->update('gmail_accounts');
        
        return TRUE;
    }
    
    /**
     * This function is used to delete the account information
     * @param number $accountId : This is account id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteAccount($accountId)
    {
        $this->mongo_db->where('login_id', (int)$accountId);
		$this->mongo_db->delete('gmail_accounts');
        
        return true;
    }

    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('gmail_accounts');
        if ($last_id) {
            $add_id = $last_id[0]['login_id'] + 1;
            return $add_id;
        } else {
            return 1;
        }
    }

}