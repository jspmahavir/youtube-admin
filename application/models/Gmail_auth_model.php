<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class gmail_auth_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('app_id','user_email');
        // Set searchable column fields
        $this->column_search = array('app_id','user_email');
        // Set default order
        $this->order = array('app_id' => 'asc');
    }

    /*
     * Fetch account data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getRows($requestData){
        $this->_get_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
        }
        $result = $this->mongo_db->get('gmail_auth_token');
        
        $finalResult = array();
        $appData = $this->mongo_db->get('app_management');
        foreach($result as $res) {
            $newResult = array();
            $newResult = $res;
            foreach($appData as $app) {
                if($res['app_id'] == $app['_id']->{'$id'}) {
                    $newResult['app_name'] = $app['app_name'];
                }
            }
            $finalResult[] = $newResult;
        }
        return $finalResult;
        // return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('gmail_auth_token');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('gmail_auth_token');
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

    function getAppAccount()
    {
        $appData = $this->mongo_db->get('app_management');
        return $appData;
    }
    
    function getUserAccount()
    {
        $userData = $this->mongo_db->get('gmail_accounts');
        $userTokenData = $this->mongo_db->get('gmail_auth_token');
        $finalUserData = array();
        foreach($userData as $user) {
            $isExists = false;
            foreach($userTokenData as $token) {
                if($user['email'] == $token['user_email']) {
                    $isExists = True;
                }
            }
            if($isExists == false) {
                $finalUserData[] = $user;
            }
        }
        return $finalUserData;
    }

    function tokenListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('user_email', $searchText, 'im', TRUE, TRUE);
        }
        $result = $this->mongo_db->get('gmail_auth_token');
        return count($result);
    }

    function tokenListing($searchText = '', $page, $segment)
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('user_email', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->limit($page)->offset($segment);
        $result = $this->mongo_db->get('gmail_auth_token');
        $finalResult = array();
        $appData = $this->mongo_db->get('app_management');
        foreach($result as $res) {
            $newResult = array();
            $newResult = $res;
            foreach($appData as $app) {
                if($res['app_id'] == $app['_id']->{'$id'}) {
                    $newResult['app_name'] = $app['app_name'];
                }
            }
            $finalResult[] = $newResult;
        }
        return $finalResult;
    }
}