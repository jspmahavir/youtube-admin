<?php
$proxyMasterId = $proxyInfo[0]['proxy_master_id'];
$proxy_url = $proxyInfo[0]['proxy_url'];
$proxy_port = $proxyInfo[0]['proxy_port'];
$username = $proxyInfo[0]['username'];
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-sitemap"></i> Proxy Management
        <small>Add / Edit Proxy</small>
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Enter Proxy Details</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    
                    <form role="form" action="<?php echo base_url() ?>proxy/editProxy" method="post" id="editProxy" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="proxy_url">Proxy URL</label>
                                        <input type="text" class="form-control" id="proxy_url" placeholder="Proxy URL" name="proxy_url" value="<?php echo $proxy_url; ?>" maxlength="128">
                                        <input type="hidden" value="<?php echo $proxyMasterId; ?>" name="proxyMasterId" id="proxyMasterId" />
                                    </div>
                                    
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="proxy_port">Proxy Port</label>
                                        <input type="text" class="form-control required proxy_port" id="proxy_port" value="<?php echo $proxy_port; ?>" name="proxy_port" maxlength="5">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input type="text" class="form-control required username" id="username" value="<?php echo $username; ?>" name="username" maxlength="128">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control" id="password" placeholder="Password" name="password" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="cpassword">Confirm Password</label>
                                        <input type="password" class="form-control" id="cpassword" placeholder="Confirm Password" name="cpassword" maxlength="20">
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mobile">Mobile Number</label>
                                        <input type="text" class="form-control required digits" id="mobile" value="<?php //echo set_value('mobile'); ?>" name="mobile" maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select class="form-control required" id="role" name="role">
                                            <option value="0">Select Role</option>
                                            <?php
                                            // if(!empty($roles))
                                            // {
                                            //     foreach ($roles as $rl)
                                            //     {
                                                    ?>
                                                    <option value="<?php //echo $rl->roleId ?>" <?php i//f($rl->roleId == set_value('role')) {echo "selected=selected";} ?>><?php //echo $rl->role ?></option>
                                                    <?php
                                            //     }
                                            // }
                                            ?>
                                        </select>
                                    </div>
                                </div>    
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="isAdmin">User Type</label>
                                        <select class="form-control required" id="isAdmin" name="isAdmin">
                                            <option value="<?= REGULAR_USER ?>">Regular User</option>
                                            <option value="<?= SYSTEM_ADMIN ?>">System Administrator</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                </div>
                            </div>  -->
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

<script src="<?php echo base_url(); ?>assets/js/proxyAddEdit.js" type="text/javascript"></script>