<?php
$configId = $configurationInfo[0]['_id']->{'$id'};
$configArray = json_decode($configurationInfo[0]['configuration'], true);
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-cog"></i> Configuration
        <small>Edit Configuration</small>
      </h1>
    </section>
    
    <section class="content">
    
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
              <!-- general form elements -->
                
                
                
                <div class="box box-primary">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="box-header">
                                <h3 class="box-title">Edit Configuration</h3>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <button type="button" name="add" id="add" class="btn btn-success" style="margin-top: 10px;">Add More</button>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <?php $this->load->helper("form"); ?>
                    <form role="form" id="editConfiguration" action="<?php echo base_url() ?>configuration/editConfiguration" method="post" role="form">
                        <input type="hidden" value="<?php echo $configId; ?>" name="configId" id="configId" />
                        <div class="box-body" id="dynamic_field">
                            <?php
                                foreach ($configArray as $label => $config) {
                            ?>
                                    <div class="row" id="row<?php echo $label; ?>">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control required" name="label[]" value="<?php echo $label; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control required" name="config[]" value="<?php echo $config; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <button type="button" name="remove" id="<?php echo $label; ?>" class="btn btn-danger btn_remove">X</button>
                                            </div>
                                        </div>
                                    </div>
                            <?php
                                }
                            ?>
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

<script type="text/javascript">
    $(document).ready(function(){
      var i=1;  
   
      $('#add').click(function(){
           i++;  
           $('#dynamic_field').append('<div class="row" id="row'+i+'"><div class="col-md-5"><div class="form-group"><input type="text" class="form-control required" name="label[]"></div></div><div class="col-md-5"><div class="form-group"><input type="text" class="form-control required" name="config[]"></div></div><div class="col-md-2"><div class="form-group"><button type="button" name="remove" id="'+i+'" class="btn btn-danger btn_remove">X</button></div></div></div>');
      });
  
      $(document).on('click', '.btn_remove', function(){  
           var button_id = $(this).attr("id");   
           $('#row'+button_id+'').remove();  
      });  
  
    });  
</script>