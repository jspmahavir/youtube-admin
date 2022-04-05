<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-user"></i> Client Management
        <small>Add, Edit, Delete</small>
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary" href="<?php echo base_url(); ?>client/add"><i class="fa fa-plus"></i> Add New</a>
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
                    <h3 class="box-title">Client List</h3>
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>client" method="POST" id="searchList">
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
                        <th>Client Name</th>
                        <th>API Key</th>
                        <th>Server IP</th>
                        <th>View Support</th>
                        <th>Comment Support</th>
                        <th>Like Support</th>
                        <th>Subscibe Support</th>
                        <th>Created On</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    <?php
                    if(!empty($clientRecords))
                    {
                        foreach($clientRecords as $record)
                        {
                    ?>
                    <tr>
                        <td><?php echo $record['client_name'] ?></td>
                        <td><?php echo $record['api_key'] ?></td>
                        <td><?php echo $record['whitelisted_server_ip'] ?></td>
                        <td><?= ($record['ytview_support'] == 1) ? 'Yes':'No'; ?></td>
                        <td><?= ($record['ytcomment_support'] == 1) ? 'Yes':'No'; ?></td>
                        <td><?= ($record['ytlike_support'] == 1) ? 'Yes':'No'; ?></td>
                        <td><?= ($record['ytsubscribe_support'] == 1) ? 'Yes':'No'; ?></td>
                        <td><?php echo date("d-m-Y", strtotime($record['created_date'])) ?></td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-info" href="<?php echo base_url().'client/edit/'.$record['authentication_id']; ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                            <a class="btn btn-sm btn-danger deleteClient" href="#" data-clientid="<?php echo $record['authentication_id']; ?>" title="Delete"><i class="fa fa-trash"></i></a>
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
            jQuery("#searchList").attr("action", baseURL + "client/" + value);
            jQuery("#searchList").submit();
        });

        //delete client start
        jQuery(document).on("click", ".deleteClient", function(){
            var clientId = $(this).data("clientid"),
                hitURL = baseURL + "client/deleteClient",
                currentRow = $(this);
            
            var confirmation = confirm("Are you sure to delete this client ?");
            
            if(confirmation)
            {
                jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : hitURL,
                data : { clientId : clientId } 
                }).done(function(data){
                    currentRow.parents('tr').remove();
                    if(data.status = true) { alert("Client successfully deleted"); }
                    else if(data.status = false) { alert("Client deletion failed"); }
                    else { alert("Access denied..!"); }
                });
            }
        });
        //delete client end
    });
</script>
