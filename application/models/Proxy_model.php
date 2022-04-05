<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Server_model (Server Model)
 * Server model class to get to handle proxy related data
 */
class Proxy_model extends CI_Model
{
    /**
     * This function is used to get the proxy listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function proxyListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->where('proxy_port', 'regexp', '/^'.$searchText.'/i');
        }
        $result = $this->mongo_db->get('proxy_master');

        // echo "<pre>";
        // print_r($result);

        // $this->db->select('BaseTbl.proxyId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_proxys as BaseTbl');
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
     * This function is used to get the proxy listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function proxyListing($searchText = '', $page, $segment)
    {
        $this->mongo_db->order_by(array('proxy_master_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('proxy_master');

        // $this->db->select('BaseTbl.proxyId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_proxys as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        // $this->db->order_by('BaseTbl.proxyId', 'DESC');
        // $this->db->limit($page, $segment);
        // $query = $this->db->get();
        
        // $result = $query->result();
        return $result;
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