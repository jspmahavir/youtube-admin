<div class="content-wrapper account">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-users"></i> Account Management
        <small>Add, Edit, Delete</small>
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary import-data"><i class="fa fa-plus"></i> Import</a>
                    <a class="btn btn-primary" href="<?php echo base_url(); ?>account/add"><i class="fa fa-plus"></i> Add New</a>
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
                    <h3 class="box-title">Account List</h3>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive no-padding">
                  <table id="acc_data_table" class="display table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Password</th>
                            <th>Recovery Email</th>
                            <th>Validation Password</th>
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
    <!-- Modal Import Data-->
    <?php $this->load->helper("form"); ?>
    <form role="form" action="<?php echo base_url() ?>account/import" method="post" enctype="multipart/form-data">
        <div class="modal fade" id="show_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Import Account List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="file" id="file" name="file"/>
            </div>
            <div class="modal-footer">
                <a href="<?php echo base_url()?>assets/sample/account.csv">Sample</a>
                <input type="submit" class="btn btn-primary" name="importSubmit" value="Import">
            </div>
            </div>
        </div>
        </div>
    </form>
    <!-- End Modal Import Data-->
</div>
<!-- <script type="text/javascript" src="<?php //echo base_url(); ?>assets/js/common.js" charset="utf-8"></script> -->
<script type="text/javascript" charset="utf-8">
    jQuery(document).ready(function(){
        jQuery('#acc_data_table').DataTable({
            processing: true,
            serverSide: true,
            order: [[4, 'desc']],
            ajax: '<?php echo base_url('account/listing'); ?>',
            columnDefs: [{
                "targets": [-1],
                "orderable": false,
                "data": null,
                "render": function(data,type,full,meta)
                { return '<a class="btn btn-sm btn-info" href="account/edit/'+data[5]+'" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp&nbsp<a class="btn btn-sm btn-danger deleteAccount" href="#" data-accountid='+data[5]+' title="Delete"><i class="fa fa-trash"></i></a>'
                }
            }]
        });

        //delete account start
        jQuery(document).on("click", ".deleteAccount", function(){
            var accountId = $(this).data("accountid"),
                hitURL = baseURL + "account/deleteAccount",
                currentRow = $(this);
            
            var confirmation = confirm("Are you sure to delete this account ?");
            
            if(confirmation)
            {
                jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : hitURL,
                data : { accountId : accountId } 
                }).done(function(data){
                    currentRow.parents('tr').remove();
                    if(data.status = true) { alert("Account successfully deleted"); }
                    else if(data.status = false) { alert("Account deletion failed"); }
                    else { alert("Access denied..!"); }
                });
            }
        });
        //delete account end

        // import data
        jQuery('.account .import-data').on('click',function(){
            $('#show_modal').modal('show');
        });
    });
</script>
