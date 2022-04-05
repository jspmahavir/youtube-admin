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
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>schedule" method="POST" id="searchList">
                            <div class="input-group">
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
                        <th class="text-center">Actions</th>
                    </tr>
                    <?php
                    if(!empty($scheduleRecords))
                    {
                        foreach($scheduleRecords as $record)
                        {
                    ?>
                    <tr>
                        <td><?php echo $record['video_url'] ?></td>
                        <td><?php echo $record['channelId'] ?></td>
                        <td><?php echo $record['video_duration'] ?></td>
                        <td><?php echo $record['scheduled_view_count'] ?></td>
                        <td><?php echo $record['completed_view_count'] ?></td>
                        <td><?php echo $record['scheduled_like_count'] ?></td>
                        <td><?php echo $record['completed_like_count'] ?></td>
                        <td><?php echo $record['scheduled_comment_count'] ?></td>
                        <td><?php echo $record['completed_comment_count'] ?></td>
                        <td><?php echo $record['scheduled_subscribe_count'] ?></td>
                        <td><?php echo $record['completed_subscribe_count'] ?></td>
                        <td><?php echo $record['keyword'] ?></td>
                        <td><?php echo date("d-m-Y", strtotime($record['created_date'])) ?></td>
                        <td class="text-center">
                            <div>
                                <form action="<?php echo base_url() ?>schedule-detail" method="post" title="View">
                                    <input type="hidden" name="schedule_id" value="<?php echo $record['schedule_data_id']; ?>"/>
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-info-circle"></i></button>
                                </form>
                                &nbsp;
                                <form action="<?php echo base_url() ?>schedule-comment-detail" method="post" title="Comment">
                                    <input type="hidden" name="schedule_id" value="<?php echo $record['schedule_data_id']; ?>"/>
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-info-circle"></i></button>
                                </form>
                                &nbsp;
                                <!-- <form action="<?php echo base_url() ?>schedule-like-detail" method="post" title="Like">
                                    <input type="hidden" name="schedule_id" value="<?php echo $record['schedule_data_id']; ?>"/>
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-info-circle"></i></button>
                                </form>
                                &nbsp;
                                <form action="<?php echo base_url() ?>schedule-subscribe-detail" method="post" title="Subscribe">
                                    <input type="hidden" name="schedule_id" value="<?php echo $record['schedule_data_id']; ?>"/>
                                    <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-info-circle"></i></button>
                                </form> -->
                            </div>
                            <!-- <a class="btn btn-sm btn-info" href="<?php //echo base_url().'schedule-detail/'.$record['schedule_data_id']; ?>" title="Details"><i class="fa fa-info-circle"></i></a> -->
                            <!-- <a class="btn btn-sm btn-danger deleteSchedule" href="#" data-scheduledataid="<?php //echo $record['schedule_data_id']; ?>" title="Delete"><i class="fa fa-trash"></i></a> -->
                        </td>
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
            jQuery("#searchList").attr("action", baseURL + "schedule/" + value);
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
