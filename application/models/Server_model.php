<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Server_model (Server Model)
 * Server model class to get to handle server related data
 */
class Server_model extends CI_Model
{
    /**
     * This function is used to get the server listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function serverListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->where('server_provider', 'regexp', '/^'.$searchText.'/i');
        }
        $result = $this->mongo_db->get('server_master');

        // echo "<pre>";
        // print_r($result);

        // $this->db->select('BaseTbl.serverId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_servers as BaseTbl');
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
     * This function is used to get the server listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function serverListing($searchText = '', $page, $segment)
    {
        $this->mongo_db->order_by(array('server_master_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('server_master');

        // $this->db->select('BaseTbl.serverId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_servers as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        // $this->db->order_by('BaseTbl.serverId', 'DESC');
        // $this->db->limit($page, $segment);
        // $query = $this->db->get();
        
        // $result = $query->result();
        return $result;
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