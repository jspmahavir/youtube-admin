<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class gmail_auth_model extends CI_Model
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
    function getAppAccount()
    {
        $appData = $this->mongo_db->get('app_management');
        return $appData;
    }
    function getUserAccount()
    {
        $userData = $this->mongo_db->get('gmail_accounts');
        return $userData;
    }

    function tokenListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->where('user_email', 'regexp', '/^'.$searchText.'/i');
        }
        $result = $this->mongo_db->get('gmail_auth_token');
        return count($result);
    }

    function tokenListing($searchText = '', $page, $segment)
    {
        $this->mongo_db->limit($page)->offset($segment);
        $result = $this->mongo_db->get('gmail_auth_token');
        $finalResult = array();
        $appData = $this->mongo_db->get('app_management');
        foreach($result as $res) {
            $newResult = array();
            foreach($appData as $app) {
                $newResult = $res;
                if($res['app_id'] == $app['_id']->{'$id'}) {
                    $newResult['app_name'] = $app['app_name'];
                }
            }
            $finalResult[] = $newResult;
        }
        return $finalResult;
    }
}