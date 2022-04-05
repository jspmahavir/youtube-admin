<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class proxy_api_model extends CI_Model
{
    private $primary_key 	= 'proxy_master_id';
	private $table_name 	= 'proxy_master';
	private $field_search 	= ['proxy_master_id', 'proxy_url', 'proxy_port', 'username', 'password', 'created_date', 'modified_date'];

	public function __construct()
	{
		$config = array(
			'primary_key' 	=> $this->primary_key,
		 	'table_name' 	=> $this->table_name,
		 	'field_search' 	=> $this->field_search,
		 );

		parent::__construct($config);
	}

	public function get_all()
	{
		$proxy_data = $this->mongo_db->order_by(array('proxy_master_id'=>'ASC'))->get($this->table_name);
		return $proxy_data;
	}
	public function check_proxy($proxy_ip,$proxy_port,$video_id)
	{
		$this->mongo_db->where(array('proxy_ip' => $proxy_ip, 'proxy_port' => $proxy_port, 'ytvideo_id' => $video_id));
		$res = $this->mongo_db->get('youtube_stats_master');
		return $res;
	}
	public function update_yt_stats($data)
	{
		$this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($data['unique_reference_id'])));
        $this->mongo_db->set(array('agent' => $data['agent'], 'status' => $data['status'], 'reason' => $data['reason'],'country' => $data['country'], 'region_name' => $data['region_name'], 'city' => $data['city'], 'zip' => $data['zip'], 'timezone' => $data['timezone'], 'isp' => $data['isp'], 'query_ip' => $data['query_ip'], 'updated_date' => date('Y/m/d H:i:s')));
		$this->mongo_db->update('youtube_stats_master');

		$this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($data['unique_reference_id'])));
		$updatedStats = $this->mongo_db->get('youtube_stats_master');

		if ($updatedStats[0]['status'] === 'success') {
			$this->mongo_db->where(array('schedule_data_id' => (int)$updatedStats[0]['schedule_id']));
			$res = $this->mongo_db->get('schedule_data');
			if ($res) {
				if ($res[0]['scheduled_view_count'] > $res[0]['completed_view_count']) {
					$view_count_add = $res[0]['completed_view_count'] + 1;
					$completed_view_percentage = 100 * $view_count_add / $res[0]['scheduled_view_count'];
					$this->mongo_db->where(array('schedule_data_id' => (int)$updatedStats[0]['schedule_id']));
					$this->mongo_db->set(array('completed_view_count' => $view_count_add, 'completed_view_percentage' => $completed_view_percentage, 'updated_date' => date('Y/m/d H:i:s')));
					$this->mongo_db->update('schedule_data');
				}
			}
		}
		return true;
	}
	public function check_generated_ip($ref_id,$generated_ip)
	{
		$this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($ref_id)));
		$res = $this->mongo_db->get('youtube_stats_master');
		if ($res) {
			$this->mongo_db->where(array('ytvideo_id' => $res[0]['ytvideo_id'], 'proxy_ip' => $res[0]['proxy_ip'], 'proxy_port' => (int)$res[0]['proxy_port'], 'status' => 'success', 'query_ip' => $generated_ip));
			$result = $this->mongo_db->get('youtube_stats_master');
			if ($result) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
}