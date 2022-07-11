<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Server_model (Server Model)
 * Server model class to get to handle server related data
 */
class Server_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('server_ip','server_provider','maximum_thread','end_point','status','created_date');
        // Set searchable column fields
        $this->column_search = array('server_ip','server_provider','maximum_thread','end_point','status','created_date');
        // Set default order
        $this->order = array('server_ip' => 'asc');
    }

    /*
     * Fetch server data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getRows($requestData){
        $this->_get_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
            // $this->db->limit($requestData['length'], $requestData['start']);
        }
        $result = $this->mongo_db->get('server_master');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('server_master');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('server_master');
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
     * This function is used to add new server to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewServer($serverInfo)
    {
        $inserted_data = $this->mongo_db->insert('server_master', $serverInfo);
        $insert_id = $this->mongo_db->get_where('server_master', array('server_master_id' => $inserted_data['server_master_id']));
        if ($insert_id) {
            return $insert_id[0]['server_master_id'];
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get server information by id
     * @param number $serverId : This is server id
     * @return array $result : This is server information
     */
    function getServerInfo($serverMasterId)
    {
        $this->mongo_db->where(array('server_master_id' => (int)$serverMasterId));
        $serverData = $this->mongo_db->get('server_master');
        return $serverData;
    }
    
    
    /**
     * This function is used to update the server information
     * @param array $serverInfo : This is servers updated information
     * @param number $serverId : This is server id
     */
    function editServer($serverInfo, $serverMasterId)
    {
        $this->mongo_db->where(array('server_master_id' => (int)$serverMasterId));
		$this->mongo_db->set(array('server_ip' => $serverInfo['server_ip'], 'server_provider' => $serverInfo['server_provider'], 'maximum_thread' => $serverInfo['maximum_thread'], 'end_point' => $serverInfo['end_point'], 'status' => $serverInfo['status'], 'modified_date' => $serverInfo['modified_date']));
        $this->mongo_db->update('server_master');
        
        return TRUE;
    }
    
    /**
     * This function is used to delete the server information
     * @param number $serverId : This is server id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteServer($serverId)
    {
        $this->mongo_db->where('server_master_id', (int)$serverId);
		$this->mongo_db->delete('server_master');
        
        return true;
    }

    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('server_master');
        if ($last_id) {
            $add_id = $last_id[0]['server_master_id'] + 1;
            return $add_id;
        } else {
            return 1;
        }
    }

}