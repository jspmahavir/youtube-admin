<?php
$clientId = $clientInfo[0]['authentication_id'];
$client_name = $clientInfo[0]['client_name'];
$api_key = $clientInfo[0]['api_key'];
$white_listed_ip = $clientInfo[0]['whitelisted_server_ip'];
$view_support = $clientInfo[0]['ytview_support'];
$comment_support = $clientInfo[0]['ytcomment_support'];
$like_support = $clientInfo[0]['ytlike_support'];
$subscribe_support = $clientInfo[0]['ytsubscribe_support'];
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-user"></i> Client Management
        <small>Add / Edit Client</small>
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Client Details</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <?php $this->load->helper("form"); ?>
                    <form role="form" id="editClient" action="<?php echo base_url() ?>client/editClient" method="post" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_name">Client Name</label>
                                        <input type="text" class="form-control required" id="client_name" name="client_name" value="<?php echo $client_name; ?>" maxlength="128">
                                        <input type="hidden" value="<?php echo $clientId; ?>" name="clientId" id="clientId" />
                                    </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="api_key">API Key</label>
                                        <input type="text" class="form-control required api_key" id="api_key" value="<?php echo $api_key; ?>" name="api_key" maxlength="128">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="white_listed_ip">White Listed IP</label>
                                        <input type="text" class="form-control required white_listed_ip" id="white_listed_ip" value="<?php echo $white_listed_ip; ?>" name="white_listed_ip" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type='checkbox' id="view_support" name="view_support" value="viewSupport" <?= ($view_support == 1) ? 'checked':''; ?>/> <label for="view_support">View</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type='checkbox' id="comment_support" name="comment_support" value="commentSupport" <?= ($comment_support == 1) ? 'checked':''; ?>/> <label for="comment_support">Comment</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type='checkbox' id="like_support" name="like_support" value="likeSupport" <?= ($like_support == 1) ? 'checked':''; ?>/> <label for="like_support">Like</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type='checkbox' id="subscribe_support" name="subscribe_support" value="subscribeSupport" <?= ($subscribe_support == 1) ? 'checked':''; ?>/> <label for="subscribe_support">Subscribe</label>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.box-body -->
    
                        <div class="box-footer">
                            <input type="submit" class="btn btn-primary" value="Submit" />
                            <input type="reset" class="btn btn-default" value="Reset" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <?php
                    $this->load->helper('form');
                    $error = $this->session->flashdata('error');
                    if($error)
                    {
                ?>
                <div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('error'); ?>                    
                </div>
                <?php } ?>
                <?php  
                    $success = $this->session->flashdata('success');
                    if($success)
                    {
                ?>
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $this->session->flashdata('success'); ?>
                </div>
                <?php } ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <?php echo validation_errors('<div class="alert alert-danger alert-dismissable">', ' <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button></div>'); ?>
                    </div>
                </div>
            </div>
        </div>    
    </section>
    
</div>
<script src="<?php echo base_url(); ?>assets/js/clientAddEdit.js" type="text/javascript"></script>