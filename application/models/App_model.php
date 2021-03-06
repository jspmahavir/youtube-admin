<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : App_model (App Model)
 * App model class to get to handle app related data
 */
class App_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('app_name','email','password','client_json','created_date');
        // Set searchable column fields
        $this->column_search = array('app_name','email','password','client_json','created_date');
        // Set default order
        $this->order = array('app_name' => 'asc');
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
        $result = $this->mongo_db->get('app_management');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('app_management');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('app_management');
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
     * This function is used to get the app listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function appListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('app_name', $searchText, 'im', TRUE, TRUE);
        }
        $result = $this->mongo_db->get('app_management');

        // echo "<pre>";
        // print_r($result);

        // $this->db->select('BaseTbl.appId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_apps as BaseTbl');
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
     * This function is used to get the app listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function appListing($searchText = '', $page, $segment)
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('app_name', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->order_by(array('_id'=>'desc'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('app_management');

        // $this->db->select('BaseTbl.appId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_apps as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        // $this->db->order_by('BaseTbl.appId', 'DESC');
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
    function checkEmailExists($email, $appId = 0)
    {
        if($appId != 0){
            $this->mongo_db->where_ne('_id', new MongoDB\BSON\ObjectID($appId));
            $this->mongo_db->where(array('email' => $email));
            $emailExist = $this->mongo_db->get('app_management');
        } else {
            $emailExist = $this->mongo_db->get_where('app_management', array('email' => $email));
        }
        return $emailExist;
    }

    /**
     * This function is used to add new app to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewApp($appInfo)
    {
        $inserted_data = $this->mongo_db->insert('app_management', $appInfo);
        $app_id = $inserted_data['_id']->{'$id'};
        $insert_id = $this->mongo_db->get_where('app_management', array('_id' => new MongoDB\BSON\ObjectID($app_id)));
        if ($insert_id) {
            return $insert_id;
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get app information by id
     * @param number $appId : This is app id
     * @return array $result : This is app information
     */
    function getAppInfo($appId)
    {
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($appId)));
        $appData = $this->mongo_db->get('app_management');
        return $appData;
    }
    
    
    /**
     * This function is used to update the app information
     * @param array $appInfo : This is apps updated information
     * @param number $appId : This is app id
     */
    function editApp($appInfo, $appId)
    {
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($appId)));
        // if ($appInfo['password']) {
            $this->mongo_db->set(array('app_name' => $appInfo['app_name'], 'email' => $appInfo['email'], 'password' => $appInfo['password'], 'client_json' => $appInfo['client_json'], 'modified_date' => $appInfo['modified_date']));
        // } else {
        //     $this->mongo_db->set(array('app_name' => $appInfo['app_name'], 'email' => $appInfo['email'], 'client_json' => $appInfo['client_json'], 'modified_date' => $appInfo['modified_date']));
        // }
        $this->mongo_db->update('app_management');
        
        return TRUE;
    }
    
    /**
     * This function is used to delete the app information
     * @param number $appId : This is app id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteApp($appId)
    {
        $this->mongo_db->where('_id', new MongoDB\BSON\ObjectID($appId));
		$this->mongo_db->delete('app_management');
        
        return true;
    }
    function getLastAppId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('app_management');
        if ($last_id) {
            $app_id = $last_id[0]['app_id'] + 1;
            return $app_id;
        } else {
            return 1;
        }
    }

}