<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Comment_model (Comment Model)
 * Comment model class to get to handle Comment related data
 */
class Comment_model extends CI_Model
{
    function __construct() {
        // Set orderable column fields
        $this->column_order = array('comment_id','video_id','comment','request','response','source','created_date');
        // Set searchable column fields
        $this->column_search = array('comment_id','video_id','comment','request','response','source','created_date');
        // Set default order
        $this->order = array('comment_id' => 'asc');
    }

    /*
     * Fetch account data from the database
     * @param $_POST filter data based on the posted parameters
     */
    public function getRows($requestData){
        $this->_get_datatables_query($requestData);
        if($requestData['length'] != -1){
            $this->mongo_db->limit($requestData['length'])->offset($requestData['start']);
        }
        $result = $this->mongo_db->get('comment_master');
        return $result;
    }
    
    /*
     * Count all records
     */
    public function countAll(){
        $result = $this->mongo_db->get('comment_master');
        return count($result);
    }
    
    /*
     * Count records based on the filter params
     * @param $_POST filter data based on the posted parameters
     */
    public function countFiltered($requestData){
        $this->_get_datatables_query($requestData);
        $query = $this->mongo_db->get('comment_master');
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
     * This function is used to get the comment listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function commentListingCount($searchText = '')
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('comment', $searchText, 'im', TRUE, TRUE);
        }
        $result = $this->mongo_db->get('comment_master');
        return count($result);
    }
    
    /**
     * This function is used to get the comment listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function commentListing($searchText = '', $page, $segment)
    {
        if(!empty($searchText)) {
            $this->mongo_db->like('comment', $searchText, 'im', TRUE, TRUE);
        }
        $this->mongo_db->order_by(array('comment_id'=>'DESC'))->limit($page)->offset($segment);
        $result = $this->mongo_db->get('comment_master');

        return $result;
    }
    
    /**
     * This function is used to add new client to system
     * @return number $insert_id : This is last inserted id
     */
    function addNewComment($commentInfo)
    {
        $inserted_data = $this->mongo_db->insert('comment_master', $commentInfo);
        $insert_id = $this->mongo_db->get_where('comment_master', array('comment_id' => $inserted_data['comment_id']));
        if ($insert_id) {
            return $insert_id[0]['comment_id'];
        } else {
            return false;
        }
    }
    
    /**
     * This function used to get comment information by id
     * @param number $commentId : This is comment id
     * @return array $result : This is comment information
     */
    function getCommentInfo($commentId)
    {
        $this->mongo_db->where(array('comment_id' => (int)$commentId));
        $commentData = $this->mongo_db->get('comment_master');
        return $commentData;
    }
    
    
    /**
     * This function is used to update the comment information
     * @param array $commentInfo : This is comments updated information
     * @param number $commentId : This is comment id
     */
    function editComment($commentInfo, $commentId)
    {
        $this->mongo_db->where(array('comment_id' => (int)$commentId));
        $this->mongo_db->set(array('comment' => $commentInfo['comment'], 'modified_date' => $commentInfo['modified_date']));
        $this->mongo_db->update('comment_master');
        
        return TRUE;
    }
    
    
    
    /**
     * This function is used to delete the comment information
     * @param number $commentId : This is comment id
     * @return boolean $result : TRUE / FALSE
     */
    function deleteComment($commentId)
    {
        $this->mongo_db->where('comment_id', (int)$commentId);
		$this->mongo_db->delete('comment_master');
        
        return true;
    }

    function getLastId()
    {
        $this->mongo_db->order_by(array('_id' => -1))->limit(1);
        $last_id = $this->mongo_db->get('comment_master');
        if ($last_id) {
            $add_id = $last_id[0]['comment_id'] + 1;
            return $add_id;
        }
    }

}