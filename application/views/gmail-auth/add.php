<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-key"></i> Gmail Authorization
        <small>Gmail Auth</small>
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Select Authorization</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <?php $this->load->helper("form"); ?>
                    <form role="form" id="authGmail" action="<?php echo base_url() ?>authorization" method="post" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="app">App Account</label>
                                        <select class="form-control required selectApp" id="app" name="app">
                                            <option value="">Select App</option>
                                            <?php
                                            if(!empty($appAccount))
                                            {
                                                foreach ($appAccount as $app)
                                                {
                                                    ?>
                                                    <option value="<?php echo $app['_id']->{'$id'}; ?>" <?php if($app['app_name'] == set_value('app')) {echo "selected=selected";} ?>><?php echo $app['app_name'] ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user">User Account</label>
                                        <select class="form-control required selectUser" id="user" name="user">
                                            <option value="">Select User</option>
                                            <?php
                                            if(!empty($userAccount))
                                            {
                                                foreach ($userAccount as $user)
                                                {
                                                    ?>
                                                    <option value="<?php echo $user['email'].'-'.$user['password']; ?>" <?php if($user['email'] == set_value('user')) {echo "selected=selected";} ?>><?php echo $user['email'] ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <span for="app" id="email"></span></br>
                                        <span for="app" id="password"></span>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.box-body -->
    
                        <div class="box-footer">
                            <input type="submit" id="submit-call" class="btn btn-primary" value="Submit" />
                            <!-- <input type="reset" class="btn btn-default" value="Reset" /> -->
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
<script src="<?php echo base_url(); ?>assets/js/appAddEdit.js" type="text/javascript"></script>
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function(){
        jQuery(document).on("change", ".selectUser", function(){
            var selectedUser = document.getElementById("user").value;
            if (selectedUser != 0) {
                const user = selectedUser.split("-");
                document.getElementById("email").innerHTML = user[0];
                document.getElementById("password").innerHTML = user[1];
            } else {
                document.getElementById("email").innerHTML = '';
                document.getElementById("password").innerHTML = '';
            }
        });

        let button = document.querySelector("#submit-call");
        button.disabled = true;
        jQuery(document).on("change", ".selectApp", function(){
            var selectedApp = document.getElementById("app").value;
            if (selectedApp) {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        });
    });
</script>