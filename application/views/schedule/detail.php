<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-calendar"></i> Schedule Detail Management
        <!-- <small>Add, Edit, Delete</small> -->
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary" href="<?php echo base_url().'schedule'; ?>"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
        <div class="row">
            <div class="col-xs-12">
              <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Schedule Detail</h3>
                    <input type="hidden" id="schedule_id" value="<?php echo $schedule_id;?>" />
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                    <table id="detail_data_table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Video ID</th>
                                <!-- <th>Server IP</th> -->
                                <th>Proxy IP</th>
                                <th>Proxy Port</th>
                                <th>Country</th>
                                <th>Region Name</th>
                                <th>City</th>
                                <th>ZIP</th>
                                <th>Timezone</th>
                                <th>ISP</th>
                                <th>Query IP</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Created On</th>
                            </tr>
                        </thead>
                    </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
            </div>
        </div>
    </section>
</div>
<!-- <script type="text/javascript" src="<?php //echo base_url(); ?>assets/js/common.js" charset="utf-8"></script> -->
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function(){
        var schid = document.getElementById('schedule_id').value;
        jQuery('#detail_data_table').DataTable({
            processing: true,
            serverSide: true,
            order: [[12, 'desc']],
            ajax: "<?php echo base_url('scheduledetail/detail?scheduleid='); ?>"+schid,
        });
    });
</script>
