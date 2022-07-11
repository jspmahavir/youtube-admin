<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-calendar"></i> Schedule Management
        <!-- <small>Add, Edit, Delete</small> -->
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <!-- <a class="btn btn-primary" href="<?php //echo base_url(); ?>schedule/add"><i class="fa fa-plus"></i> Add New</a> -->
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
                    <h3 class="box-title">Schedule List</h3>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                    <table id="sche_data_table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Video ID</th>
                                <th>Channel ID</th>
                                <th>Video Duration</th>
                                <th>Scheduled View Count</th>
                                <th>Completed View Count</th>
                                <th>Scheduled Like Count</th>
                                <th>Completed Like Count</th>
                                <th>Scheduled Comment Count</th>
                                <th>Completed Comment Count</th>
                                <th>Scheduled Subscribe Count</th>
                                <th>Completed Subscribe Count</th>
                                <th>Keyword</th>
                                <th>Created On</th>
                                <th>Status / Do</th>
                                <th>Actions</th>
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
        jQuery('#sche_data_table').DataTable({
            processing: true,
            serverSide: true,
            order: [[12, 'desc']],
            ajax: '<?php echo base_url('schedule/listing'); ?>',
            columnDefs: [{
                "targets": [13],
                "orderable": false,
                "data": null,
                "render": function(data,type,full,meta)
                {
                    console.log(data);
                    if(data[13] == 1) {
                        return '<p>Running /</p><a class="btn btn-sm updateSchedule" href="#" data-scheduledataid='+data[14]+' title="Stop"><input type="button" class="btn btn-primary" value="Stop"><input type="hidden" name="schedule_status_id" id="'+data[14]+'_schedule_status_id" value="0"/></a>'
                    } else {
                        return '<p>Stopped /</p><a class="btn btn-sm updateSchedule" href="#" data-scheduledataid='+data[14]+' title="Start"><input type="button" class="btn btn-primary" value="Start"><input type="hidden" name="schedule_status_id" id="'+data[14]+'_schedule_status_id" value="1"/></a>'
                    }
                }
            },
            {
                "targets": [-1],
                "orderable": false,
                "data": null,
                "render": function(data,type,full,meta)
                { 
                    return '<div><form action="scheduledetail" method="post" title="Schedule Detail"><input type="hidden" name="schedule_id" value="'+data[14]+'"/><button type="submit" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></button></form>&nbsp;<form action="commentdetail" method="post" title="Comments"><input type="hidden" name="schedule_id" value="'+data[14]+'"/><button type="submit" class="btn btn-sm btn-info"><i class="fa fa-comment"></i></button></form></div>'
                }
            }]
        });

        jQuery(document).on("click", ".updateSchedule", function(){
            var scheduleMasterId = $(this).data("scheduledataid");
            var hitURL = baseURL + "schedule/updateSchedule";
            var fieldId = "#"+scheduleMasterId+"_schedule_status_id";
            var scheduleStatusId = jQuery(fieldId).val();
            var confirmation = confirm("Are you sure to Update this schedule ?");
            
            if(confirmation)
            {
                jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : hitURL,
                data : { scheduleMasterId : scheduleMasterId, scheduleStatusId: scheduleStatusId } 
                }).done(function(data){
                    if(data.status = true) { 
                        var uri = baseURL + "schedule";
                        window.location.href = uri;
                    }
                    else if(data.status = false) { alert("Schedule updation failed"); }
                    else { alert("Access denied..!"); }
                });
            }

        });

        //delete schedule start
        jQuery(document).on("click", ".deleteSchedule", function(){
            var scheduleMasterId = $(this).data("schedulemasterid"),
                hitURL = baseURL + "schedule/deleteSchedule",
                currentRow = $(this);
            
            var confirmation = confirm("Are you sure to delete this schedule ?");
            
            if(confirmation)
            {
                jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : hitURL,
                data : { scheduleMasterId : scheduleMasterId } 
                }).done(function(data){
                    currentRow.parents('tr').remove();
                    if(data.status = true) { alert("Schedule successfully deleted"); }
                    else if(data.status = false) { alert("Schedule deletion failed"); }
                    else { alert("Access denied..!"); }
                });
            }
        });
        //delete schedule end
    });
</script>
