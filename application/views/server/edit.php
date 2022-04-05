<?php
$serverMasterId = $serverInfo[0]['server_master_id'];
$server_ip = $serverInfo[0]['server_ip'];
$server_provider = $serverInfo[0]['server_provider'];
$maximum_thread = $serverInfo[0]['maximum_thread'];
$end_point = $serverInfo[0]['end_point'];
$status = $serverInfo[0]['status'];
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-server"></i> Server Management
        <small>Add / Edit Server</small>
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Server Details</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    
                    <form role="form" action="<?php echo base_url() ?>server/editServer" method="post" id="editServer" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="server_ip">Server IP</label>
                                        <input type="text" class="form-control" id="server_ip" placeholder="Server IP" name="server_ip" value="<?php echo $server_ip; ?>" maxlength="20">
                                        <input type="hidden" value="<?php echo $serverMasterId; ?>" name="serverMasterId" id="serverMasterId" />
                                    </div>
                                    
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="server_provider">Server Provider</label>
                                        <input type="text" class="form-control" id="server_provider" placeholder="Server Provider" name="server_provider" value="<?php echo $server_provider; ?>" maxlength="128">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="maximum_thread">Maximum Thread</label>
                                        <input type="text" class="form-control" id="maximum_thread" value="<?php echo $maximum_thread; ?>" name="maximum_thread" maxlength="128">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_point">End-Point</label>
                                        <input type="text" class="form-control" id="end_point" value="<?php echo $end_point; ?>" name="end_point" maxlength="128">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="end_point">Status</label>
                                                    <select class="form-control required selectApp" id="status" name="status">
                                                        <option value="">--Status--</option>
                                                        <option value="active" <?php if($status == 'active') { echo 'selected'; } ?>>Active</option>
                                                        <option value="inactive" <?php if($status == 'inactive') { echo 'selected'; } ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
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

<script src="<?php echo base_url(); ?>assets/js/serverAddEdit.js" type="text/javascript"></script>