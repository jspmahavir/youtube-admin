<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Server_model (Server Model)
 * Server model class to get to handle proxy related data
 */
class Proxy_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('proxy_master_id','proxy_url','proxy_port','username','created_date');
        // Set searchable column fields
        $this->column_search = array('proxy_master_id','proxy_url','proxy_port','username','created_date');
        // Set default order
        $this->order = array('proxy_master_id' => 'asc');
    }

    /*
     * Fetch proxy data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getRows($requestData){
        $this->_get_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
            // $this->db->limit($requestData['length'], $requestData['start']);
        }
        $result = $this->mongo_db->get('proxy_master');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('proxy_master');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('proxy_master');
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
     * This function is used to add new proxy to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewProxy($proxyInfo)
    {
        $inserted_data = $this->mongo_db->insert('proxy_master', $proxyInfo);
        $insert_id = $this->mongo_db->get_where('proxy_master', array('proxy_master_id' => $inserted_data['proxy_master_id']));
        if ($insert_id) {
            return $insert_id[0]['proxy_master_id'];
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get proxy information by id
     * @param number $proxyId : This is proxy id
     * @return array $result : This is proxy information
     */
    function getProxyInfo($proxyMasterId)
    {
        $this->mongo_db->where(array('proxy_master_id' => (int)$proxyMasterId));
        $proxyData = $this->mongo_db->get('proxy_master');
        return $proxyData;
    }
    
    
    /**
     * This function is used to update the proxy information
     * @param array $proxyInfo : This is proxys updated information
     * @param number $proxyId : This is proxy id
     */
    function editProxy($proxyInfo, $proxyMasterId)
    {
        $this->mongo_db->where(array('proxy_master_id' => (int)$proxyMasterId));
        if ($proxyInfo['password']) {
            $this->mongo_db->set(array('proxy_url' => $proxyInfo['proxy_url'], 'proxy_port' => $proxyInfo['proxy_port'],'username' => $proxyInfo['username'], 'password' => $proxyInfo['password'], 'modified_date' => $proxyInfo['modified_date']));
        } else {
            $this->mongo_db->set(array('proxy_url' => $proxyInfo['proxy_url'], 'proxy_port' => $proxyInfo['proxy_port'],'username' => $proxyInfo['username'], 'modified_date' => $proxyInfo['modified_date']));
        }
        $this->mongo_db->update('proxy_master');
        
        return TRUE;
    }
    
    
    /**
     * This function is used to delete the proxy information
     * @param number $proxyId : This is proxy id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteProxy($proxyId)
    {
        $this->mongo_db->where('proxy_master_id', (int)$proxyId);
		$this->mongo_db->delete('proxy_master');
        
        return true;
    }

    /**
     * This function is used to delete the proxy information
     * @return boolean $result : TRUE / FALSE
     */
    function deleteAll()
    {
		$this->mongo_db->delete_all('proxy_master');
        return true;
    }

    /*
     * Import proxy data into the database
     * @param $data data to be insert based on the passed parameters
     */
    public function importProxyData($data = array()) {
        if(!empty($data)){
            $this->mongo_db->insert('proxy_master', $data);
            return true;
        }
        return false;
    }

    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('proxy_master');
        if ($last_id) {
            $add_id = $last_id[0]['proxy_master_id'] + 1;
            return $add_id;
        }
    }

}