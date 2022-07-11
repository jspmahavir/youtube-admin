<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Client_model (Client Model)
 * Client model class to get to handle client related data
 */
class Client_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('client_name','api_key','whitelisted_server_ip','ytview_support','ytcomment_support','ytlike_support','ytsubscribe_support','created_date');
        // Set searchable column fields
        $this->column_search = array('client_name','api_key','whitelisted_server_ip','ytview_support','ytcomment_support','ytlike_support','ytsubscribe_support','created_date');
        // Set default order
        $this->order = array('client_name' => 'asc');
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
        $result = $this->mongo_db->get('api_authentication');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('api_authentication');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('api_authentication');
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
     * This function is used to get the client listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function clientListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('client_name', $searchText, 'im', TRUE, TRUE);
        }
        $result = $this->mongo_db->get('api_authentication');

        // echo "<pre>";
        // print_r($result);

        // $this->db->select('BaseTbl.clientId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_clients as BaseTbl');
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
     * This function is used to get the client listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function clientListing($searchText = '', $page, $segment)
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('client_name', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->order_by(array('authentication_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('api_authentication');

        // $this->db->select('BaseTbl.clientId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_clients as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        // $this->db->order_by('BaseTbl.clientId', 'DESC');
        // $this->db->limit($page, $segment);
        // $query = $this->db->get();
        
        // $result = $query->result();
        return $result;
    }
    
    /**
     * This function is used to add new client to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewClient($clientInfo)
    {
        $inserted_data = $this->mongo_db->insert('api_authentication', $clientInfo);
        $insert_id = $this->mongo_db->get_where('api_authentication', array('authentication_id' => $inserted_data['authentication_id']));
        if ($insert_id) {
            return $insert_id[0]['authentication_id'];
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get client information by id
     * @param number $clientId : This is client id
     * @return array $result : This is client information
     */
    function getClientInfo($clientId)
    {
        $this->mongo_db->where(array('authentication_id' => (int)$clientId));
        $clientData = $this->mongo_db->get('api_authentication');
        return $clientData;
    }
    
    
    /**
     * This function is used to update the client information
     * @param array $clientInfo : This is clients updated information
     * @param number $clientId : This is client id
     */
    function editClient($clientInfo, $clientId)
    {
        $this->mongo_db->where(array('authentication_id' => (int)$clientId));
        $this->mongo_db->set(array('client_name' => $clientInfo['client_name'], 'api_key' => $clientInfo['api_key'],'whitelisted_server_ip' => $clientInfo['whitelisted_server_ip'], 'ytview_support' => $clientInfo['ytview_support'], 'ytcomment_support' => $clientInfo['ytcomment_support'], 'ytlike_support' => $clientInfo['ytlike_support'], 'ytsubscribe_support' => $clientInfo['ytsubscribe_support'], 'modified_date' => $clientInfo['modified_date']));
        $this->mongo_db->update('api_authentication');
        
        return TRUE;
    }
    
    
    
    /**
     * This function is used to delete the client information
     * @param number $clientId : This is client id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteClient($clientId)
    {
        $this->mongo_db->where('authentication_id', (int)$clientId);
		$this->mongo_db->delete('api_authentication');
        
        return true;
    }

    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('api_authentication');
        if ($last_id) {
            $add_id = $last_id[0]['authentication_id'] + 1;
            return $add_id;
        }
    }

}