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
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                    <table id="cli_data_table" class="display table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Client Name</th>
                                <th>API Key</th>
                                <th>Server IP</th>
                                <th>View Support</th>
                                <th>Comment Support</th>
                                <th>Like Support</th>
                                <th>Subscibe Support</th>
                                <th>Created On</th>
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
        jQuery('#cli_data_table').DataTable({
            processing: true,
            serverSide: true,
            order: [[7, 'desc']],
            ajax: '<?php echo base_url('client/listing'); ?>',
            columnDefs: [{
                "targets": [-1],
                "orderable": false,
                "data": null,
                "render": function(data,type,full,meta)
                { return '<a class="btn btn-sm btn-info" href="client/edit/'+data[8]+'" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp&nbsp<a class="btn btn-sm btn-danger deleteClient" href="#" data-clientid='+data[8]+' title="Delete"><i class="fa fa-trash"></i></a>'
                }
            }]
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
