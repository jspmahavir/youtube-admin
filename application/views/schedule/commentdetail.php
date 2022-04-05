<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-calendar"></i> Schedule Comment Details
        <!-- <small>Add, Edit, Delete</small> -->
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary" href="javascript:history.go(-1)"><i class="fa fa-arrow-left"></i> Back</a>
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
                    <h3 class="box-title">Schedule Comment Detail</h3>
                    <div class="box-tools">
                        <form method="POST" id="searchList">
                            <div class="input-group">
                                <input type="hidden" value="<?php echo $scheduleId; ?>" name="schedule_id" id="schedule_id" />
                                <input type="text" name="searchText" value="<?php echo $searchText; ?>" class="form-control input-sm pull-right" style="width: 150px;" placeholder="Search"/>
                                <div class="input-group-btn">
                                    <button class="btn btn-sm btn-default searchList"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                  <table class="table table-hover">
                    <tr>
                        <th>Video</th>
                        <th>Comment</th>
                        <th>User Email</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Created On</th>
                    </tr>
                    <?php
                    if(!empty($scheduleCommentDetails))
                    {
                        foreach($scheduleCommentDetails as $record)
                        {
                    ?>
                    <tr>
                        <td><?php echo $record['video_id'] ?></td>
                        <td><?php echo @$record['comment'] ?></td>
                        <td><?php echo $record['user_email'] ?></td>
                        <td><?php echo $record['status'] ?></td>
                        <td><?php echo ($record['status'] == 'success') ? '-' : $record['error_msg'] ?></td>
                        <td><?php echo isset($record['created']) ? date("d-m-Y", strtotime($record['created'])) : '-' ?></td>
                    </tr>
                    <?php
                        }
                    }
                    ?>
                  </table>
                  
                </div><!-- /.box-body -->
                <div class="box-footer clearfix">
                    <?php echo $this->pagination->create_links(); ?>
                </div>
              </div><!-- /.box -->
            </div>
        </div>
    </section>
</div>
<!-- <script type="text/javascript" src="<?php //echo base_url(); ?>assets/js/common.js" charset="utf-8"></script> -->
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function(){
        jQuery('ul.pagination li a').click(function (e) {
            e.preventDefault();
            var link = jQuery(this).get(0).href;
            var value = link.substring(link.lastIndexOf('/') + 1);
            jQuery("#searchList").attr("action", baseURL + "schedule-comment-detail/" + value);
            jQuery("#searchList").submit();
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