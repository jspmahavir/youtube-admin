<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Account_model (Account Model)
 * Account model class to get to handle account related data
 */
class Account_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('email','password','recovery_email','email_validation_pass','created_date');
        // Set searchable column fields
        $this->column_search = array('email','password','recovery_email','email_validation_pass','created_date');
        // Set default order
        $this->order = array('email' => 'asc');
    }

    /*
     * Fetch account data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getRows($requestData){
        $this->_get_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
            // $this->db->limit($requestData['length'], $requestData['start']);
        }
        $result = $this->mongo_db->get('gmail_accounts');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('gmail_accounts');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('gmail_accounts');
        return count($query);
    }
    
    /*
     * Perform the SQL queries needed for an server-side processing requested
     * @param $_POST filter data based on the posted parameters
     */
    private function _get_datatables_query($requestData){
        // loop searchable columns 
        foreach($this->column_search as $item){
            if($requestData['search']['value']){
                $this->mongo_db->or_like($item, $requestData['search']['value'], 'im', TRUE, TRUE);
            }
        }
         
        if(isset($requestData['order'])){
            $this->mongo_db->order_by(array($this->column_order[$requestData['order']['0']['column']] => $requestData['order']['0']['dir']));
        }else if(isset($this->order)){
            $order = $this->order;
            $this->mongo_db->order_by(array(key($order) => $order[key($order)]));
        }
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
        $this->mongo_db->set(array('name' => $accountInfo['name'], 'email' => $accountInfo['email'], 'password' => $accountInfo['password'], 'recovery_email' => $accountInfo['recovery_email'], 'email_validation_pass' => $accountInfo['email_validation_pass'] , 'modified_date' => $accountInfo['modified_date']));
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

    /*
     * Import account data into the database
     * @param $data data to be insert based on the passed parameters
     */
    public function importAccountData($data = array()) {
        if(!empty($data)){
            $this->mongo_db->insert('gmail_accounts', $data);
            return true;
        }
        return false;
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