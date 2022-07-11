<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class schedule_api_model extends CI_Model
{
    private $primary_key 	= 'schedule_data_id';
    private $table_name 	= 'schedule_data';
    private $field_search 	= ['schedule_data_id', 'authentication_id', 'video_url', 'video_duration', 'scheduled_view_count', 'completed_view_count', 'scheduled_like_count'];

	public function __construct()
	{
		$config = array(
			'primary_key' 	=> $this->primary_key,
             'table_name' 	=> $this->table_name,
             'field_search' 	=> $this->field_search,
		 );

		parent::__construct($config);
	}

	public function check_status($security_token,$schedulling_id,$type)
	{
		$this->mongo_db->where(array('schedule_data_id' => (int)$schedulling_id));
        $schedule_detail = $this->mongo_db->get('schedule_data');
        if ($schedule_detail) {
            $auth_id = $schedule_detail[0]['authentication_id'];
            $this->mongo_db->where(array('authentication_id' => (int)$auth_id, 'api_key' => $security_token));
            $auth_detail = $this->mongo_db->get('api_authentication');
            $schedule_data['schedule_data'] = $schedule_detail;
            $schedule_data['auth_data'] = $auth_detail;
            return $schedule_data;
        } else {
            $schedule_data['schedule_data'] = [];
            $schedule_data['auth_data'] = [];
            return $schedule_data;
        }
    }
    public function stop_schedule($security_token,$schedulling_id)
	{
        $this->mongo_db->where(array('api_key' => $security_token));
        $auth_detail = $this->mongo_db->get('api_authentication');
        if ($auth_detail) {
            $this->mongo_db->where(array('schedule_data_id' => (int)$schedulling_id));
		    $this->mongo_db->set(array('status' => 0));
            $this->mongo_db->update('schedule_data');
            return true;
        } else {
            return false;
        }
    }
    public function check_key($key)
	{
        $this->mongo_db->where(array('api_key' => $key));
        $auth_detail = $this->mongo_db->get('api_authentication');
		return $auth_detail;
    }
    // public function check_plan($key, $view, $like, $comment, $subscribe)
    // {
    //     $this->mongo_db->where(array('api_key' => $key));
    //     $auth_detail = $this->mongo_db->get('api_authentication');
    // }
    public function get_last_id()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('schedule_data');
        if ($last_id) {
            return $last_id[0]['schedule_data_id'] + 1;
        } else {
            return 1;
        }
    }
    public function add_user_request($lastId, $auth_id, $yt_url, $keyword, $view, $like, $comment, $subscribe, $duration, $channel_id)
    {
        $created_date = date("Y-m-d h:i:s");
		$updated_date = date("Y-m-d h:i:s");

		$save_data = [
			"schedule_data_id" => (int)$lastId,
			"authentication_id" => $auth_id,
			"video_url" => $yt_url,
            "channelId" => $channel_id,
			"video_duration" => (double)$duration,
			"scheduled_view_count" => (int)$view,
			"completed_view_count" => 0,
			"scheduled_like_count" => (int)$like,
			"completed_like_count" => 0,
			"scheduled_comment_count" => (int)$comment,
			"completed_comment_count" => 0,
            "scheduled_subscribe_count" => (int)$subscribe,
            "completed_subscribe_count" => 0,
            "created_date" => $created_date,
            "updated_date" => $updated_date,
            "status" => 1,
            "keyword" => $keyword,
            "total_comment" => 20
		];
			
		$save_details = $this->mongo_db->insert('schedule_data',$save_data);
		return $save_details;
    }
    public function get_last_schedulelog_id()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('schedule_data_log');
        if ($last_id) {
            return $last_id[0]['schedule_data_log_id'];
        } else {
            return 1;
        }
    }
    public function add_schedule_log($log_data)
    {
		$save_details = $this->mongo_db->insert('schedule_data_log',$log_data);
		return $save_details;
    }
    public function update_schedule_log($schedule_log_id, $log_data)
    {
		$this->mongo_db->where(array('schedule_data_log_id' => (int)$schedule_log_id));
        $this->mongo_db->set(array('schedule_data_id' => $log_data['schedule_data_id'], 'server_master_id' => $log_data['server_master_id'], 'video_url' => $log_data['video_url'], 'data_process_server_ip' => $log_data['data_process_server_ip'], 'data_process_proxy_ip' => $log_data['data_process_proxy_ip'], 'port' => $log_data['port'], 'request_end_time' => $log_data['request_end_time'], 'status' => $log_data['status'], 'request_type' => $log_data['request_type'], 'remarks' => $log_data['remarks'], 'channelId' => $log_data['channel_id']));
        $this->mongo_db->update('schedule_data_log');
		return true;
    }
}