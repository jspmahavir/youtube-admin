<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Comment (CommentController)
 * Comment Class to control all server related operations.
 */
class Comment extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('comment_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the server
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Comment List';
        $this->loadViews("comment/list", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to load the server list
     */
    function listing()
    {
        $data = $row = array();
        
        // Fetch comment's records
        $cmtData = $this->comment_model->getRows($_REQUEST);
        
        $i = $_REQUEST['start'];
        foreach($cmtData as $comment){
            $i++;
            $created = date( 'jS M Y', strtotime($comment['created_date']));
            $video_id = isset($comment['video_id']) ? $comment['video_id'] : '';
            $request = isset($comment['request']) ? $comment['request'] : '';
            $response = isset($comment['response']) ? json_encode($comment['response']) : '';
            $source = isset($comment['source']) ? $comment['source'] : '';
            $data[] = array($comment['comment_id'], $video_id, $comment['comment'], $request, $response, $source, $created);
        }
        $output = array(
            "draw" => $_REQUEST['draw'],
            "recordsTotal" => $this->comment_model->countAll(),
            "recordsFiltered" => $this->comment_model->countFiltered($_REQUEST),
            "data" => $data,
        );
        
        // Output to JSON format
        echo json_encode($output);
    }

    /**
     * This function is used to load the add new form
     */
    function add()
    {
        $this->load->model('client_model');
        $this->global['pageTitle'] = 'YouTube Viewer : Add New Comment';

        $this->loadViews("comment/add", $this->global, NULL, NULL);
    }
    
    /**
     * This function is used to add new server to the system
     */
    function addNewComment()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('comment','Comment','required|max_length[250]');
        if($this->form_validation->run() == FALSE)
        {
            $this->add();
        }
        else
        {
            $comment = $this->input->post('comment');
            $lastId = $this->comment_model->getLastId();
            if(empty($lastId)) {
                $lastId = 1;
            }
            $commentInfo = array('comment_id'=>$lastId, 'comment'=>$comment, 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'));
            
            $this->load->model('comment_model');
            $result = $this->comment_model->addNewComment($commentInfo);
            
            if($result > 0){
                $this->session->set_flashdata('success', 'Add new comment successfully');
            } else {
                $this->session->set_flashdata('error', 'Add new comment failed');
            }
            redirect('comment/listing');
        }
    }

    
    /**
     * This function is used load server edit information
     * @param number $serverId : Optional : This is server id
     */
    function edit($commentId = NULL)
    {
        if($commentId == null)
        {
            redirect('comment/listing');
        }
        
        $data['commentInfo'] = $this->comment_model->getCommentInfo($commentId);
        $data['commentId'] = $commentId;

        $this->global['pageTitle'] = 'YouTube Viewer : Edit Comment';
        
        $this->loadViews("comment/edit", $this->global, $data, NULL);
    }
    
    
    /**
     * This function is used to edit the client information
     */
    function editComment()
    {
        $this->load->library('form_validation');

        $commentId = $this->input->post('commentId');
        
        $this->form_validation->set_rules('comment','Comment','required|max_length[256]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->edit($commentId);
        }
        else
        {
            $comment = $this->input->post('comment');
            
            $commentInfo = array('comment'=>$comment, 'modified_date'=>date('Y-m-d H:i:s'));
            
            $result = $this->comment_model->editComment($commentInfo, $commentId);
            
            if($result == true)
            {
                $this->session->set_flashdata('success', 'Comment updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Comment updation failed');
            }
            
            redirect('comment/listing');
        }
    }


    /**
     * This function is used to delete the server using serverId
     * @return boolean $result : TRUE / FALSE
     */
    function deleteComment()
    {
        $commentId = $this->input->post('commentId');
        $result = $this->comment_model->deleteComment($commentId);
        
        if ($result > 0) {
            echo(json_encode(array('status' => TRUE)));
        } else {
            echo(json_encode(array('status' => FALSE)));
        }
    }
    
    /**
     * Page not found : error 404
     */
    function pageNotFound()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : 404 - Page Not Found';
        
        $this->loadViews("404", $this->global, NULL, NULL);
    }
}

?>