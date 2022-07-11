<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Configuration_model (Configuration Model)
 * Configuration model class to get to handle configuration related data
 */
class Configuration_model extends CI_Model
{
    /**
     * This function is used to get the configuration listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function configurationListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->where('configuration_provider', 'regexp', '/^'.$searchText.'/i');
        }
        $result = $this->mongo_db->get('configuration_master');

        // echo "<pre>";
        // print_r($result);

        // $this->db->select('BaseTbl.configurationId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_configurations as BaseTbl');
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
     * This function is used to get the configuration listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function configurationListing($searchText = '', $page, $segment)
    {
        $this->mongo_db->order_by(array('_id'=> -1))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('configuration_master');

        return $result;
    }
    
    /**
     * This function is used to add new configuration to system
     * @return number $insert_id : This is last inserted id
     */
    function editConfiguration($configurationInfo)
    {
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($configurationInfo['id'])));
		$this->mongo_db->set(array('configuration' => $configurationInfo['configuration'], 'modified_date' => $configurationInfo['modified_date']));
        $this->mongo_db->update('configuration_master');
        
        return TRUE;
    }
    
    /**
     * This function used to get configuration information by id
     * @param number $configurationId : This is configuration id
     * @return array $result : This is configuration information
     */
    function getConfigurationInfo()
    {
        // $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($configurationMasterId)));
        $configurationData = $this->mongo_db->get('configuration_master');
        return $configurationData;
    }
    
    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('configuration_master');
        if ($last_id) {
            $add_id = $last_id[0]['configuration_master_id'] + 1;
            return $add_id;
        } else {
            return 1;
        }
    }

}