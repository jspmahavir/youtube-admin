<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Schedule_model (Schedule Model)
 * Schedule model class to get to handle schedule related data
 */
class Schedule_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('video_url','channelId','video_duration','scheduled_view_count','completed_view_count','scheduled_like_count','completed_like_count','scheduled_comment_count','completed_comment_count','scheduled_subscribe_count','completed_subscribe_count','keyword','created_date');
        // Set searchable column fields
        $this->column_search = array('video_url','channelId','video_duration','scheduled_view_count','completed_view_count','scheduled_like_count','completed_like_count','scheduled_comment_count','completed_comment_count','scheduled_subscribe_count','completed_subscribe_count','keyword','created_date');
        // Set default order
        $this->order = array('video_url' => 'asc');

        // Set orderable column fields for schedule detail
        $this->schdetail_column_order = array('ytvideo_id','proxy_ip','proxy_port','country','
        region_name','city','zip','timezone','isp','query_ip','status','reason','created_date');
        // Set searchable column fields for schedule detail
        $this->schdetail_column_search = array('ytvideo_id','proxy_ip','proxy_port','country','
        region_name','city','zip','timezone','isp','query_ip','status','reason','created_date');
        // Set default order for schedule detail
        $this->schdetail_order = array('ytvideo_id' => 'asc');

        // Set orderable column fields for comment detail
        $this->cmtdetail_column_order = array('video_id','comment','user_email','status','
        error_msg','created');
        // Set searchable column fields for comment detail
        $this->cmtdetail_column_search = array('video_id','comment','user_email','status','
        error_msg','created');
        // Set default order for comment detail
        $this->cmtdetail_order = array('video_id' => 'asc');
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
        $result = $this->mongo_db->get('schedule_data');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('schedule_data');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('schedule_data');
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

    /*
     * Fetch account data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getSchDetailRows($requestData){
        $this->_get_schdetail_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
            // $this->db->limit($requestData['length'], $requestData['start']);
        }
        $this->mongo_db->where(array('schedule_id' => (int)$requestData['scheduleid']));
        $result = $this->mongo_db->get('youtube_stats_master');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countSchDetailAll($requestData){
        $this->mongo_db->where(array('schedule_id' => (int)$requestData['scheduleid']));
        $result = $this->mongo_db->get('youtube_stats_master');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countSchDetailFiltered($requestData){
        $this->_get_schdetail_datatables_query($requestData);
        $this->mongo_db->where(array('schedule_id' => (int)$requestData['scheduleid']));
        $query = $this->mongo_db->get('youtube_stats_master');
        return count($query);
    }
    
    /*
     * Perform the SQL queries needed for an server-side processing requested
     * @param $_POST filter data based on the posted parameters
     */
    private function _get_schdetail_datatables_query($requestData){
        // loop searchable columns 
        foreach($this->schdetail_column_search as $item){
            if($requestData['search']['value']){
                $this->mongo_db->or_like($item, $requestData['search']['value'], 'im', TRUE, TRUE);
            }
        }
         
        if(isset($requestData['order'])){
            $this->mongo_db->order_by(array($this->schdetail_column_order[$requestData['order']['0']['column']] => $requestData['order']['0']['dir']));
        }else if(isset($this->schdetail_order)){
            $order = $this->schdetail_order;
            $this->mongo_db->order_by(array(key($order) => $order[key($order)]));
        }
    }

    /*
     * Fetch account data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getCmtDetailRows($requestData){
        $this->_get_cmtdetail_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
            // $this->db->limit($requestData['length'], $requestData['start']);
        }
        $this->mongo_db->where(array('schedule_id' => (int)$requestData['scheduleid']));
        $result = $this->mongo_db->get('youtube_comment_stats');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countCmtDetailAll($requestData){
        $this->mongo_db->where(array('schedule_id' => (int)$requestData['scheduleid']));
        $result = $this->mongo_db->get('youtube_comment_stats');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countCmtDetailFiltered($requestData){
        $this->_get_cmtdetail_datatables_query($requestData);
        $this->mongo_db->where(array('schedule_id' => (int)$requestData['scheduleid']));
        $query = $this->mongo_db->get('youtube_comment_stats');
        return count($query);
    }
    
    /*
     * Perform the SQL queries needed for an server-side processing requested
     * @param $_POST filter data based on the posted parameters
     */
    private function _get_cmtdetail_datatables_query($requestData){
        // loop searchable columns 
        foreach($this->cmtdetail_column_search as $item){
            if($requestData['search']['value']){
                $this->mongo_db->or_like($item, $requestData['search']['value'], 'im', TRUE, TRUE);
            }
        }
         
        if(isset($requestData['order'])){
            $this->mongo_db->order_by(array($this->cmtdetail_column_order[$requestData['order']['0']['column']] => $requestData['order']['0']['dir']));
        }else if(isset($this->cmtdetail_order)){
            $order = $this->cmtdetail_order;
            $this->mongo_db->order_by(array(key($order) => $order[key($order)]));
        }
    }

    function scheduleCommentDetailCount($scheduleId, $searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('comment', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $result = $this->mongo_db->get('youtube_comment_stats');

        return count($result);
    }
    
    function scheduleLikeDetailCount($scheduleId, $searchText = '')
    {
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $result = $this->mongo_db->get('youtube_like_stats');

        return count($result);
    }

    function scheduleSubscribeDetailCount($scheduleId, $searchText = '')
    {
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $result = $this->mongo_db->get('youtube_subscribe_stats');

        return count($result);
    }

    function getScheduleDetail($scheduleId, $searchText = '', $page, $segment)
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('proxy_ip', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $this->mongo_db->order_by(array('_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('youtube_stats_master');

        // $this->db->select('BaseTbl.scheduleId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.createdDtm, Role.role');
        // $this->db->from('tbl_schedules as BaseTbl');
        // $this->db->join('tbl_roles as Role', 'Role.roleId = BaseTbl.roleId','left');
        // if(!empty($searchText)) {
        //     $likeCriteria = "(BaseTbl.email  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.name  LIKE '%".$searchText."%'
        //                     OR  BaseTbl.mobile  LIKE '%".$searchText."%')";
        //     $this->db->where($likeCriteria);
        // }
        // $this->db->where('BaseTbl.isDeleted', 0);
        // $this->db->where('BaseTbl.roleId !=', 1);
        // $this->db->order_by('BaseTbl.scheduleId', 'DESC');
        // $this->db->limit($page, $segment);
        // $query = $this->db->get();
        
        // $result = $query->result();
        return $result;
    }

    function getScheduleCommentDetail($scheduleId, $searchText = '', $page, $segment)
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('comment', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $this->mongo_db->order_by(array('_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('youtube_comment_stats');

        return $result;
    }

    function getScheduleLikeDetail($scheduleId, $searchText = '', $page, $segment)
    {
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $this->mongo_db->order_by(array('_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('youtube_like_stats');

        return $result;
    }

    function getScheduleSubscribeDetail($scheduleId, $searchText = '', $page, $segment)
    {
        $this->mongo_db->where(array('schedule_id' => (int)$scheduleId));
        $this->mongo_db->order_by(array('_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('youtube_subscribe_stats');

        return $result;
    }

    /**
     * This function is used to check whether email id is already exist or not
     * @param {string} $email : This is email id
     * @param {number} $scheduleId : This is schedule id
     * @return {mixed} $result : This is searched result
     */
    function checkEmailExists($email, $scheduleId = 0)
    {
        $this->db->select("email");
        $this->db->from("tbl_schedules");
        $this->db->where("email", $email);   
        $this->db->where("isDeleted", 0);
        if($scheduleId != 0){
            $this->db->where("scheduleId !=", $scheduleId);
        }
        $query = $this->db->get();

        return $query->result();
    }
    
    
    /**
     * This function is used to add new schedule to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewSchedule($scheduleInfo)
    {
        $inserted_data = $this->mongo_db->insert('schedule_master', $scheduleInfo);
        $insert_id = $this->mongo_db->get_where('schedule_master', array('schedule_master_id' => $inserted_data['schedule_master_id']));
        if ($insert_id) {
            return $insert_id[0]['schedule_master_id'];
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get schedule information by id
     * @param number $scheduleId : This is schedule id
     * @return array $result : This is schedule information
     */
    function getScheduleInfo($scheduleId)
    {
        $this->mongo_db->where(array('schedule_data_id' => (int)$scheduleId));
        $scheduleData = $this->mongo_db->get('schedule_data');
        return $scheduleData;
    }
    
    
    /**
     * This function is used to update the schedule information
     * @param array $scheduleInfo : This is schedules updated information
     * @param number $scheduleId : This is schedule id
     */
    function editSchedule($scheduleInfo, $scheduleMasterId)
    {
        $this->mongo_db->where(array('schedule_master_id' => (int)$scheduleMasterId));
        if ($scheduleInfo['password']) {
            $this->mongo_db->set(array('schedule_url' => $scheduleInfo['schedule_url'], 'schedule_port' => $scheduleInfo['schedule_port'],'username' => $scheduleInfo['username'], 'password' => $scheduleInfo['password'], 'modified_date' => $scheduleInfo['modified_date']));
        } else {
            $this->mongo_db->set(array('schedule_url' => $scheduleInfo['schedule_url'], 'schedule_port' => $scheduleInfo['schedule_port'],'username' => $scheduleInfo['username'], 'modified_date' => $scheduleInfo['modified_date']));
        }
        $this->mongo_db->update('schedule_master');
        
        return TRUE;
    }
    
    
    
    /**
     * This function is used to delete the schedule information
     * @param number $scheduleId : This is schedule id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteSchedule($scheduleId)
    {
        $this->mongo_db->where('schedule_master_id', (int)$scheduleId);
		$this->mongo_db->delete('schedule_master');
        
        return true;
    }


    /**
     * This function is used to match schedules password for change password
     * @param number $scheduleId : This is schedule id
     */
    function matchOldPassword($scheduleId, $oldPassword)
    {
        $this->db->select('scheduleId, password');
        $this->db->where('scheduleId', $scheduleId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get('tbl_schedules');
        
        $schedule = $query->result();

        if(!empty($schedule)){
            if(verifyHashedPassword($oldPassword, $schedule[0]->password)){
                return $schedule;
            } else {
                return array();
            }
        } else {
            return array();
        }
    }
    
    /**
     * This function is used to change schedules password
     * @param number $scheduleId : This is schedule id
     * @param array $scheduleInfo : This is schedule updation info
     */
    function changePassword($scheduleId, $scheduleInfo)
    {
        $this->db->where('scheduleId', $scheduleId);
        $this->db->where('isDeleted', 0);
        $this->db->update('tbl_schedules', $scheduleInfo);
        
        return $this->db->affected_rows();
    }


    /**
     * This function is used to get schedule login history
     * @param number $scheduleId : This is schedule id
     */
    function loginHistoryCount($scheduleId, $searchText, $fromDate, $toDate)
    {
        $this->db->select('BaseTbl.scheduleId, BaseTbl.sessionData, BaseTbl.machineIp, BaseTbl.scheduleAgent, BaseTbl.agentString, BaseTbl.platform, BaseTbl.createdDtm');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.sessionData LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        if(!empty($fromDate)) {
            $likeCriteria = "DATE_FORMAT(BaseTbl.createdDtm, '%Y-%m-%d' ) >= '".date('Y-m-d', strtotime($fromDate))."'";
            $this->db->where($likeCriteria);
        }
        if(!empty($toDate)) {
            $likeCriteria = "DATE_FORMAT(BaseTbl.createdDtm, '%Y-%m-%d' ) <= '".date('Y-m-d', strtotime($toDate))."'";
            $this->db->where($likeCriteria);
        }
        if($scheduleId >= 1){
            $this->db->where('BaseTbl.scheduleId', $scheduleId);
        }
        $this->db->from('tbl_last_login as BaseTbl');
        $query = $this->db->get();
        
        return $query->num_rows();
    }

    /**
     * This function is used to get schedule login history
     * @param number $scheduleId : This is schedule id
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function loginHistory($scheduleId, $searchText, $fromDate, $toDate, $page, $segment)
    {
        $this->db->select('BaseTbl.scheduleId, BaseTbl.sessionData, BaseTbl.machineIp, BaseTbl.scheduleAgent, BaseTbl.agentString, BaseTbl.platform, BaseTbl.createdDtm');
        $this->db->from('tbl_last_login as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.sessionData  LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        if(!empty($fromDate)) {
            $likeCriteria = "DATE_FORMAT(BaseTbl.createdDtm, '%Y-%m-%d' ) >= '".date('Y-m-d', strtotime($fromDate))."'";
            $this->db->where($likeCriteria);
        }
        if(!empty($toDate)) {
            $likeCriteria = "DATE_FORMAT(BaseTbl.createdDtm, '%Y-%m-%d' ) <= '".date('Y-m-d', strtotime($toDate))."'";
            $this->db->where($likeCriteria);
        }
        if($scheduleId >= 1){
            $this->db->where('BaseTbl.scheduleId', $scheduleId);
        }
        $this->db->order_by('BaseTbl.id', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }

    /**
     * This function used to get schedule information by id
     * @param number $scheduleId : This is schedule id
     * @return array $result : This is schedule information
     */
    function getScheduleInfoById($scheduleId)
    {
        $this->db->select('scheduleId, name, email, mobile, roleId');
        $this->db->from('tbl_schedules');
        $this->db->where('isDeleted', 0);
        $this->db->where('scheduleId', $scheduleId);
        $query = $this->db->get();
        
        return $query->row();
    }

    /**
     * This function used to get schedule information by id with role
     * @param number $scheduleId : This is schedule id
     * @return aray $result : This is schedule information
     */
    function getScheduleInfoWithRole($scheduleId)
    {
        $this->db->select('BaseTbl.scheduleId, BaseTbl.email, BaseTbl.name, BaseTbl.mobile, BaseTbl.isAdmin, BaseTbl.roleId, Roles.role');
        $this->db->from('tbl_schedules as BaseTbl');
        $this->db->join('tbl_roles as Roles','Roles.roleId = BaseTbl.roleId');
        $this->db->where('BaseTbl.scheduleId', $scheduleId);
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }

    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('schedule_master');
        if ($last_id) {
            $add_id = $last_id[0]['schedule_master_id'] + 1;
            return $add_id;
        }
    }


    /**
     * This function is used to update the schedule information
     * @param number $scheduleId : This is schedules updated information
     * @param number $scheduleStatusId : This is schedule id
     */
    function updateSchedule($scheduleId, $scheduleStatusId)
    {
        $this->mongo_db->where(array('schedule_data_id' => (int)$scheduleId));
        $this->mongo_db->set(array('status' => (int)$scheduleStatusId));
        $this->mongo_db->update('schedule_data');
        
        return TRUE;
    }

}