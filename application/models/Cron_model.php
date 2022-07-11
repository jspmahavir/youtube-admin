<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class cron_model extends CI_Model
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

    public function getRandomScheduledData($method)
    {
        // $getAction = array('random', 'min', 'max');
        $getAction = array('random');
        $action = $getAction[array_rand($getAction)];
        
        $this->mongo_db->where(array('status' => 1));
        $scheduledData = $this->mongo_db->get('schedule_data');
        $randomData = array();
        if($method == 'view') {
            foreach($scheduledData as $sdata) {
                if($sdata['completed_view_count'] < $sdata['scheduled_view_count']) {
                    $sdata['remaining_count'] = $sdata['scheduled_view_count'] - $sdata['completed_view_count'];
                    $randomData[] = $sdata;
                }
            }
        }
        if($method == 'like') {
            foreach($scheduledData as $sdata) {
                if($sdata['completed_like_count'] < $sdata['scheduled_like_count']) {
                    $randomData[] = $sdata;
                }
            }
        }
        if($method == 'subscribe') {
            foreach($scheduledData as $sdata) {
                if($sdata['completed_subscribe_count'] < $sdata['scheduled_subscribe_count']) {
                    $randomData[] = $sdata;
                }
            }
        }
        if($method == 'comment') {
            $commentIntervalDuration = '900';
            foreach($scheduledData as $sdata) {
                $currentDateTime = strtotime(date('Y/m/d H:i:s'));
                $lastCommentDateTime = strtotime($sdata['comment_add_datetime']);
                $timeDiff = $currentDateTime - $lastCommentDateTime;
                if($sdata['completed_comment_count'] < $sdata['scheduled_comment_count'] && $timeDiff > $commentIntervalDuration) {
                    $randomData[] = $sdata;
                }
            }
        }
        if($randomData) {
            if($method == 'view') {
                if($action == 'max') {
                    array_multisort(array_column($randomData, 'remaining_count'), SORT_DESC, $randomData);
                    $record = $randomData[0];
                } else if ($action == 'min') {
                    array_multisort(array_column($randomData, 'remaining_count'), SORT_DESC, $randomData);
                    $key = count($randomData) - 1;
                    $record = $randomData[$key];
                } else {
                    $key = array_rand($randomData);
                    $record = $randomData[$key];
                }
            } else {
                $key = array_rand($randomData);
                $record = $randomData[$key];
            }
        } else {
            $record = array();
        }
        return $record;
    }

    public function getScheduledData($random_schedule, $call)
    {
        // $this->mongo_db->where(array('schedule_data_id' => (int)$schedule_id));
        // $schedule_data = $this->mongo_db->get('schedule_data');
        if ($random_schedule) {
            if ($random_schedule['status'] == 1) {
                $authentication_id = $random_schedule['authentication_id'];
                $this->mongo_db->where(array('authentication_id' => (int)$authentication_id));
                $validatePlan = $this->mongo_db->get('api_authentication');
                if ($validatePlan) {
                    $view_support = $validatePlan[0]['ytview_support'];
                    $comment_support = $validatePlan[0]['ytcomment_support'];
                    $like_support = $validatePlan[0]['ytlike_support'];
                    $subscribe_support = $validatePlan[0]['ytsubscribe_support'];

                    $schedule_view = $random_schedule['scheduled_view_count'];
                    $complete_view = $random_schedule['completed_view_count'];
                    $schedule_comment = $random_schedule['scheduled_comment_count'];
                    $complete_comment = $random_schedule['completed_comment_count'];
                    $schedule_like = $random_schedule['scheduled_like_count'];
                    $complete_like = $random_schedule['completed_like_count'];
                    $schedule_subscribe = $random_schedule['scheduled_subscribe_count'];
                    $complete_subscribe = $random_schedule['completed_subscribe_count'];

                    if ($schedule_view === $complete_view && $schedule_comment === $complete_comment && $schedule_like === $complete_like && $schedule_subscribe === $complete_subscribe) {
                        $this->mongo_db->where(array('schedule_data_id' => (int)$schedule_id));
                        $this->mongo_db->set(array('status' => 0));
                        $this->mongo_db->update('schedule_data');
                    }

                    if($call == 'view' && $view_support === 1 && $schedule_view > $complete_view) {
                        return $random_schedule;
                    } else if($call == 'like' && $like_support === 1 && $schedule_like > $complete_like) {
                        return $random_schedule;
                    } else if($call == 'subscribe' && $subscribe_support === 1 && $schedule_subscribe > $complete_subscribe) {
                        return $random_schedule;
                    } else if($call == 'comment' && $comment_support === 1 && $schedule_comment > $complete_comment) {
                        return $random_schedule;
                    } else {
                        $random_schedule = [];
                        return $random_schedule;
                    }
                }
            } else {
                $random_schedule = [];
                return $random_schedule;
            }
        }
    }
    public function checkServerAvailability()
    {
        $data = $this->mongo_db->order_by(array('server_master_id'=>'ASC'))->get('server_master');

        // $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        // $last_id = $this->mongo_db->get('server_master');
        // $random_id = rand(1,$last_id[0]['server_master_id']);

        // $this->mongo_db->where(array('server_master_id' => $random_id));
        // $data = $this->mongo_db->get('server_master');

        // if ($server_data) {
        //     $this->mongo_db->where(array('server_master_id' => (int)$server_data[0]['server_master_id'], 'status' => 'complete'))->limit(1);
        //     $available_server = $this->mongo_db->get('server_stats_master');
        //     return $available_server;
        // }

        // foreach ($data as $server_data) {
        //     $this->mongo_db->where(array('server_master_id' => $server_data['server_master_id']));
        //     $available_server = $this->mongo_db->get('server_stats_master');
            
        //     if ($available_server) {
        //         if ($available_server[0]['status'] === 'complete' || $available_server[0]['status'] === 'error') {
        //             return $server_data;
        //         }
        //     }
        // }
		return $data;
    }
    public function checkProxyAvailability($yt_url)
    {
        $configuration['proxyUse'] = 'random';
        // get all proxies
        $allProxy = $this->mongo_db->get('proxy_master');
        if($configuration['proxyUse'] == 'unique') {
            // get all proxies which are used for specific video.
            $this->mongo_db->where(array('ytvideo_id' => $yt_url, 'status' => 'success'));
            $usedProxy = $this->mongo_db->get('youtube_stats_master');
            $availableProxy = array();

            if($allProxy) {
                foreach($allProxy as $proxy) {
                    $isExits = false;
                    if($usedProxy) {
                        foreach($usedProxy as $used) {
                            if($proxy['proxy_url'] == $used['proxy_ip'] && $proxy['proxy_port'] == $used['proxy_port']) {
                                $isExits = true;
                            }
                        }
                        if($isExits == false) {
                            $availableProxy[] = $proxy;
                        }
                    } else {
                        $availableProxy[] = $proxy;
                    }
                }
            }
            $key = array_rand($availableProxy);
            $record = $availableProxy[$key];
            return $record;
        } else { 
            $key = array_rand($allProxy);
            $record = $allProxy[$key];
            return $record;
        }
    }

    public function viewLogCount($schedule_id)
    {
        $this->mongo_db->where(array('schedule_data_id' => $schedule_id));
        $viewCount = $this->mongo_db->get('schedule_data');
        return $viewCount;
    }

    public function insertYTStatistics($proxy_ip,$proxy_port,$yt_url,$schedule_id,$server_id, $server_ip)
	{
        $data = array('schedule_id' => $schedule_id, 'server_master_id' => $server_id, 'server_ip' => $server_ip, 'proxy_ip' => $proxy_ip, 'proxy_port' => $proxy_port, 'ytvideo_id' => $yt_url, 'agent' => '', 'status' => 'Send', 'reason' =>'', 'country' => '', 'region_name' => '', 'city' => '', 'zip' => '', 'timezone' => '', 'isp' => '', 'query_ip' => '', 'created_date' => date('Y/m/d H:i:s'), 'updated_date' => date('Y/m/d H:i:s'));
        $save_details = $this->mongo_db->insert('youtube_stats_master',$data);
        
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('youtube_stats_master');
        if ($last_id) {
            return $last_id[0]['_id']->{'$id'};
        }
		// return $save_details;
	}
    
    public function getRandomAuthTokenLike($video_id, $count) {
        // get all token data
        $auth_token_data = $this->mongo_db->get('gmail_auth_token');
        $availableToken = array();
        $finalToken = array();
        $this->mongo_db->where(array('video_id' => $video_id, 'status' => "success"));
        $usedToken = $this->mongo_db->get('youtube_like_stats');
        
        foreach($auth_token_data as $tokenData) {
            $isExits = false;
            if($usedToken) {
                foreach($usedToken as $used) {
                    if($tokenData['refresh_token'] == $used['refresh_token'] && $tokenData['user_email'] == $used['user_email']) {
                        $isExits = true;
                    }
                }
                if($isExits == false) {
                    $availableToken[] = $tokenData;
                }
            } else {
                $availableToken[] = $tokenData;
            }
        }
        if($count > count($availableToken)) {
            $count = count($availableToken);
        }

        if($count > 0) {
            if($availableToken) {
                $token_key = array_rand($availableToken, $count);
                if(is_array($token_key)) {
                    for($i=0;$i<$count; $i++) {
                        $finalToken[] = $availableToken[$token_key[$i]];
                    }
                } else {
                    $finalToken[] = $availableToken[$token_key];
                }
            } else {
                $token_key = array_rand($auth_token_data, $count);
                if(is_array($token_key)) {
                    for($i=0;$i<$count; $i++) {
                        $finalToken[] = $auth_token_data[$token_key[$i]];
                    }
                } else {
                    $finalToken[] = $auth_token_data[$token_key];
                }
            }
            return $finalToken;
        } else {
            return $finalToken;
        }
    }

    /**
     * This function is used to add new app to system
     * @return number $stats_id : This is last inserted id
     */
    public function addLikeStats($statsInfo) {
        if($statsInfo) {
            $inserted_data = $this->mongo_db->insert('youtube_like_stats', $statsInfo);
            return $stats_id = $inserted_data['_id']->{'$id'};
        }
    }

    /**
     * This function is used to update the app information
     * @param array $statsInfo : This is stats updated information
     * @param number $stats_id : This is stats id
     */
    public function updateLikeStats($statsInfo, $stats_id)
    {
        if($stats_id) {
            $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($stats_id)));
            $this->mongo_db->set(array('status' => $statsInfo['status'], 'error_msg' => @$statsInfo['error_msg'], 'like_count' => @$statsInfo['like_count']));
            $this->mongo_db->update('youtube_like_stats');
            return true;
        }
    }

    public function updateLikeCounter($yt_video_id) {
        if($yt_video_id) {
            $this->mongo_db->where(array('video_url' => $yt_video_id));
            $video_scheduleData = $this->mongo_db->get('schedule_data')[0];
            $videoCount = $video_scheduleData['completed_like_count'] + 1;

            // Update Schedule for Like Counter
            $this->mongo_db->where(array('video_url' => $yt_video_id));
            $this->mongo_db->set(array('completed_like_count' => $videoCount));
            $this->mongo_db->update('schedule_data');
        }
    }

    public function getRandomAuthTokenSubscribe($channel_id, $count) {
        // get all token data
        $auth_token_data = $this->mongo_db->get('gmail_auth_token');
        $availableToken = array();
        $finalToken = array();
        $this->mongo_db->where(array('channel_id' => $channel_id, 'status' => "success"));
        $usedToken = $this->mongo_db->get('youtube_subscribe_stats');

        foreach($auth_token_data as $tokenData) {
            $isExits = false;
            if($usedToken) {
                foreach($usedToken as $used) {
                    if($tokenData['refresh_token'] == $used['refresh_token'] && $tokenData['user_email'] == $used['user_email']) {
                        $isExits = true;
                    }
                }
                if($isExits == false) {
                    $availableToken[] = $tokenData;
                }
            } else {
                $availableToken[] = $tokenData;
            }
        }

        if($count > count($availableToken)) {
            $count = count($availableToken);
        }

        if($count > 0) {
            if($availableToken) {
                $token_key = array_rand($availableToken, $count);
                if(is_array($token_key)) {
                    for($i=0;$i<$count; $i++) {
                        $finalToken[] = $availableToken[$token_key[$i]];
                    }
                } else {
                    $finalToken[] = $availableToken[$token_key];
                }
            } else {
                $token_key = array_rand($auth_token_data, $count);
                if(is_array($token_key)) {
                    for($i=0;$i<$count; $i++) {
                        $finalToken[] = $auth_token_data[$token_key[$i]];
                    }
                } else {
                    $finalToken[] = $auth_token_data[$token_key];
                }
            }
            return $finalToken;
        } else {
            return $finalToken;
        }
    }

    public function getRandomAuthTokenComment($ytVideoId, $count) {
        // get all token data
        $auth_token_data = $this->mongo_db->get('gmail_auth_token');
        $finalToken = array();

        $this->mongo_db->where(array('video_id' => $ytVideoId, 'status' => "success"));
        $usedToken = $this->mongo_db->get('youtube_comment_stats');

        foreach($auth_token_data as $tokenData) {
            $isExits = false;
            if($usedToken) {
                foreach($usedToken as $used) {
                    if($tokenData['refresh_token'] == $used['refresh_token'] && $tokenData['user_email'] == $used['user_email']) {
                        $isExits = true;
                    }
                }
                if($isExits == false) {
                    $availableToken[] = $tokenData;
                }
            } else {
                $availableToken[] = $tokenData;
            }
        }

        if($count > count($auth_token_data)) {
            $count = count($auth_token_data);
        }
        if($count > 0) {
            if($availableToken) {
                $token_key = array_rand($availableToken, $count);
                if(is_array($token_key)) {
                    for($i=0;$i<$count; $i++) {
                        $finalToken[] = $availableToken[$token_key[$i]];
                    }
                } else {
                    $finalToken[] = $availableToken[$token_key];
                }
            } else {
                if($auth_token_data) {
                    $token_key = array_rand($auth_token_data, $count);
                    if(is_array($token_key)) {
                        for($i=0;$i<$count; $i++) {
                            $finalToken[] = $auth_token_data[$token_key[$i]];
                        }
                    } else {
                        $finalToken[] = $auth_token_data[$token_key];
                    }
                }
            }
            return $finalToken;
        } else {
            return $finalToken;
        }
    }

    /**
     * This function is used to add new app to system
     * @return number $stats_id : This is last inserted id
     */
    public function addSubscribeStats($statsInfo) {
        if($statsInfo) {
            $inserted_data = $this->mongo_db->insert('youtube_subscribe_stats', $statsInfo);
            return $stats_id = $inserted_data['_id']->{'$id'};
        }
    }

    /**
     * This function is used to update the app information
     * @param array $statsInfo : This is stats updated information
     * @param number $stats_id : This is stats id
     */
    public function updateSubscribeStats($statsInfo, $stats_id)
    {
        if($stats_id) {
            $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($stats_id)));
            $this->mongo_db->set(array('status' => $statsInfo['status'], 'error_msg' => @$statsInfo['error_msg'], 'subscribe_count' => @$statsInfo['subscribe_count']));
            $this->mongo_db->update('youtube_subscribe_stats');
            return true;
        }
    }

    public function updateSubscribeCounter($channelId) {
        if($channelId) {
            $this->mongo_db->where(array('channelId' => $channelId));
            $video_scheduleData = $this->mongo_db->get('schedule_data')[0];
            $videoCount = $video_scheduleData['completed_subscribe_count'] + 1;

            // Update Schedule for Subscribe Counter
            $this->mongo_db->where(array('channelId' => $channelId));
            $this->mongo_db->set(array('completed_subscribe_count' => $videoCount));
            $this->mongo_db->update('schedule_data');
        }
    }

    /**
     * This function is used to add new app to system
     * @return number $stats_id : This is last inserted id
     */
    public function addCommentStats($statsInfo) {
        if($statsInfo) {
            $inserted_data = $this->mongo_db->insert('youtube_comment_stats', $statsInfo);
            return $stats_id = $inserted_data['_id']->{'$id'};
        }
    }

    /**
     * This function is used to update the app information
     * @param array $statsInfo : This is stats updated information
     * @param number $stats_id : This is stats id
     */
    public function updateCommentStats($statsInfo, $stats_id)
    {
        if($stats_id) {
            $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($stats_id)));
            $this->mongo_db->set(array('status' => $statsInfo['status'], 'error_msg' => @$statsInfo['error_msg'], 'comment_count' => @$statsInfo['comment_count']));
            $this->mongo_db->update('youtube_comment_stats');
            return true;
        }
    }

    public function updateCommentCounter($scheduleDataId) {
        if($scheduleDataId) {
            $this->mongo_db->where(array('schedule_data_id' => $scheduleDataId));
            $video_scheduleData = $this->mongo_db->get('schedule_data')[0];
            $videoCount = $video_scheduleData['completed_comment_count'] + 1;

            // Update Schedule for Like Counter
            $this->mongo_db->where(array('schedule_data_id' => $scheduleDataId));
            $this->mongo_db->set(array('completed_comment_count' => $videoCount, 'comment_add_datetime' => date('Y/m/d H:i:s')));
            $this->mongo_db->update('schedule_data');
        }
    }

    public function removeStatsEntries($dateTime) {
        if($dateTime) {
            $this->mongo_db->where_or(array('status' => 'Send', 'status' => 'Good Proxy', 'reason' => 'Working..'));
            $this->mongo_db->where_lt('created_date', $dateTime);
            $data = $this->mongo_db->delete_all('youtube_stats_master');
        }
    }

    public function getServerData() {
        $this->mongo_db->where(array('status' => 'active'));
        $data = $this->mongo_db->get('server_master');
        return $data;
    }

    public function checkServerStatsFromLog($serverId) {
        $logData = array();
        if($serverId) {
            $this->mongo_db->where(array('server_master_id' => $serverId));
            $this->mongo_db->where(array('status' => 'Send'));
            $this->mongo_db->delete_all('youtube_stats_master');
            
            sleep(1);

            $this->mongo_db->where(array('server_master_id' => $serverId));
            $this->mongo_db->where_or(array('status' => 'InProgress'));
            $this->mongo_db->where_or(array('status' => 'Good Proxy'));
            $logData = $this->mongo_db->get('youtube_stats_master');
        }
        return $logData;
    }


    public function getUniqeComment($ytVideoId) {
        $this->mongo_db->where(array('video_id' => $ytVideoId, 'source' => "drafter"));
        $commentData = $this->mongo_db->get('comment_master');
        if(empty($commentData)) {
            $this->mongo_db->where(array('video_id' => $ytVideoId));
            $commentData = $this->mongo_db->get('comment_master');
        }
        $availableData = array();
        $this->mongo_db->where(array('video_id' => $ytVideoId, 'status' => "success"));
        $usedData = $this->mongo_db->get('youtube_comment_stats');
        foreach($commentData as $aData) {
            $isExits = false;
            if($usedData) {
                foreach($usedData as $used) {
                    if($aData['comment'] == $used['comment']) {
                        $isExits = true;
                    }
                }
                if($isExits == false) {
                    $availableData[] = $aData;
                }
            } else {
                $availableData[] = $aData;
            }
        }

        $key = array_rand($availableData);
        $record = $availableData[$key];

        return $record['comment'];
    }

    public function getUniqeGeneralComment($ytVideoId) {
        $commentData = $this->mongo_db->get('comment_master');
        $generalComments = array();
        foreach($commentData as $comment) {
            if(!isset($comment['video_id'])) {
                $generalComments[] = $comment;
            }
        }
        $availableData = array();
        $this->mongo_db->where(array('video_id' => $ytVideoId, 'status' => "success"));
        $usedData = $this->mongo_db->get('youtube_comment_stats');
        foreach($generalComments as $aData) {
            $isExits = false;
            if($usedData) {
                foreach($usedData as $used) {
                    if($aData['comment'] == $used['comment']) {
                        $isExits = true;
                    }
                }
                if($isExits == false) {
                    $availableData[] = $aData;
                }
            } else {
                $availableData[] = $aData;
            }
        }

        $key = array_rand($availableData);
        $record = $availableData[$key];

        return $record['comment'];
    }
    

    public function updateTokenInfo($data, $info)
    {
        $authTokenId = $info['_id']->{'$id'};
        if($authTokenId) {
            $updateArr = array();
            $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($authTokenId)));
            foreach($data as $key => $val) {
                $updateArr[$key] = $val;
            }
            $this->mongo_db->set($updateArr);
            $this->mongo_db->update('gmail_auth_token');
        }

        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($authTokenId)));
        $auth_token_data = $this->mongo_db->get('gmail_auth_token')[0];
        return $auth_token_data;
    }

    public function getTranscriptVideoIds() {
        $this->mongo_db->where(array('status' => 1));
        $scheduledData = $this->mongo_db->get('schedule_data');

        $generatedTrans = $this->mongo_db->get('youtube_video_transcript');
        $availableVideo = array();

        if($scheduledData) {
            foreach($scheduledData as $schedule) {
                $isExits = false;
                if($generatedTrans) {
                    foreach($generatedTrans as $generate) {
                        if($generate['video_id'] == $schedule['video_url']) {
                            $isExits = true;
                        }
                    }
                    if($isExits == false) {
                        $temp = array();
                        $temp['video_url'] = $schedule['video_url'];
                        $temp['total_comment'] = $schedule['total_comment'];
                        $temp['scheduled_comments'] = $schedule['scheduled_comment_count'];
                        $availableVideo[] = $temp;
                    }
                } else {
                    $temp = array();
                    $temp['video_url'] = $schedule['video_url'];
                    $temp['total_comment'] = $schedule['total_comment'];
                    $temp['scheduled_comments'] = $schedule['scheduled_comment_count'];
                    $availableVideo[] = $temp;
                }
            }
        }
        return $availableVideo;
    }

    public function addtranscriptDesc($transInfo) {
        if($transInfo) {
            $inserted_data = $this->mongo_db->insert('youtube_video_transcript', $transInfo);
            return $stats_id = $inserted_data['_id']->{'$id'};
        }
    }

    public function getVideoTranscriptIds() {
        $transcriptData = array();
        $this->mongo_db->where(array('status' => "success"));
        $transcriptData = $this->mongo_db->get('youtube_video_transcript');
        $finalData = array();
        foreach($transcriptData as $transcript) {
            if($transcript['comment'] < $transcript['total_comment']) {
                $finalData[] = $transcript;
            }
        }
        return $finalData;
    }

    public function getRandomProxyforComments()
    {
        // get all proxies
        $allProxy = $this->mongo_db->get('proxy_master');
        $key = array_rand($allProxy);
        $record = $allProxy[$key];
        return $record;
        exit();
        // will do it later with for checking already used for same or not...

        if($configuration['proxyUse'] == 'unique') {
            // get all proxies which are used for specific video.
            $this->mongo_db->where(array('ytvideo_id' => $yt_url, 'status' => 'success'));
            $usedProxy = $this->mongo_db->get('youtube_stats_master');
            $availableProxy = array();

            if($allProxy) {
                foreach($allProxy as $proxy) {
                    $isExits = false;
                    if($usedProxy) {
                        foreach($usedProxy as $used) {
                            if($proxy['proxy_url'] == $used['proxy_ip'] && $proxy['proxy_port'] == $used['proxy_port']) {
                                $isExits = true;
                            }
                        }
                        if($isExits == false) {
                            $availableProxy[] = $proxy;
                        }
                    } else {
                        $availableProxy[] = $proxy;
                    }
                }
            }
            $key = array_rand($availableProxy);
            $record = $availableProxy[$key];
            return $record;
        } else { 
            $key = array_rand($allProxy);
            $record = $allProxy[$key];
            return $record;
        }
    }

    public function addvideoComment($commentInfo) {
        $stats_id = "";
        if($commentInfo) {
            $this->mongo_db->where(array('comment' => $commentInfo['comment'], 'video_id' => $commentInfo['video_id']));
            $isAvailable = $this->mongo_db->get('comment_master');
            if(empty($isAvailable)) {
                $inserted_data = $this->mongo_db->insert('comment_master', $commentInfo);
                $stats_id = $inserted_data['_id']->{'$id'};
            }
        }
        return $stats_id;
    }

    function getCommentLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('comment_master');
        if ($last_id) {
            $add_id = $last_id[0]['comment_id'] + 1;
            return $add_id;
        } else {
            return '1';
        }
    }


    function updateTranscriptCommentCounter($tid, $wfid) {
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($tid)));
        $videoTranscript = $this->mongo_db->get('youtube_video_transcript')[0];
        // $commentCount = 0;
        // if ($wfid === '82') { //if comment type is question then, add 3
            $commentCount = $videoTranscript['comment'] + 1;
        // } else {
        //     $commentCount = $videoTranscript['comment'] + 6;
        // }

        $updateCount = 0;
        //update comment counter to stop 
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($tid)));
        if ($wfid === '110') {
            $updateCount = $videoTranscript['comment_ratio'] - 1;
            $this->mongo_db->set(array('comment' => $commentCount, 'comment_ratio' => $updateCount));
        } else if ($wfid === '82') {
            $updateCount = $videoTranscript['question_ratio'] - 1;
            $this->mongo_db->set(array('comment' => $commentCount, 'question_ratio' => $updateCount));
        } else if ($wfid === '142') {
            $updateCount = $videoTranscript['opinion_ratio'] - 1;
            $this->mongo_db->set(array('comment' => $commentCount, 'opinion_ratio' => $updateCount));
        }
        $this->mongo_db->update('youtube_video_transcript');

    }

    function getVideoTranscript() {
        $this->mongo_db->where(array('status' => "success"));
        $transcriptData = $this->mongo_db->get('youtube_video_transcript');
        return $transcriptData;
    }

    public function getVideoTranscriptForComment() {
        $transcriptData = array();
        $this->mongo_db->where(array('status' => "success"));
        $transcriptData = $this->mongo_db->get('youtube_video_transcript');
        $finalData = array();
        foreach($transcriptData as $transcript) {
            if($transcript['comment'] < $transcript['total_comment']) {
                $finalData[] = $transcript;
            }
        }
        return $finalData;
    }

    function updateTranscriptwithjson($stringAry, $tid) {
        if($tid) {
            $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($tid)));
            $this->mongo_db->set(array('commentArr' => $stringAry));
            $this->mongo_db->update('youtube_video_transcript');
        }
    }

    //add workflow execution for get id of added comments in drafter
    public function addWorkflowExecution($transid, $wfid, $exid, $request, $response) {
        if($transid && $wfid && $exid) {
            $commentType = '';
            if ($wfid === '110') {
                $commentType = '6 comments added';
            } else if ($wfid === '82') {
                $commentType = '3 questions added';
            } else if ($wfid === '142') {
                $commentType = '6 opinions added';
            }
            $data = array('execution_id' => $exid, 'transcript_id' => $transid, 'workflow_id' => $wfid, 'ex_message' => $commentType, 'request' => $request, 'response' => $response, 'status' => 'remaining', 'created_date' => date('Y/m/d H:i:s'));
            $this->mongo_db->insert('workflow_execution_stats', $data);
            return true;
        } else {
            return false;
        }
    }

    //get workflow execution data
    public function getWorkflowExecution() {
        $executionData = array();
        $this->mongo_db->where(array('status' => 'remaining'));
        $executionData = $this->mongo_db->get('workflow_execution_stats');
        return $executionData;
    }

    //get transcript data by id
    public function getVideoTranscriptById($trid) {
        $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($trid)));
        $transciptData = $this->mongo_db->get('youtube_video_transcript');
        return $transciptData;
    }

    //update workflow execution status
    function updateWorkflowExStatus($wfobjid) {
        if($wfobjid) {
            $this->mongo_db->where(array('_id' => new MongoDB\BSON\ObjectID($wfobjid)));
            $this->mongo_db->set(array('status' => 'completed'));
            $this->mongo_db->update('workflow_execution_stats');
        }
    }
}