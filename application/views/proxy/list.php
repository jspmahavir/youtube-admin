<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-sitemap"></i> Proxy Management
        <small>Add, Edit, Delete</small>
      </h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12 text-right">
                <div class="form-group">
                    <a class="btn btn-primary import-data"><i class="fa fa-plus"></i> Import</a>
                    <a class="btn btn-primary" href="<?php echo base_url(); ?>proxy/add"><i class="fa fa-plus"></i> Add New</a>
                    <!-- <a class="btn btn-danger deleteAll" href="#"><i class="fa fa-trash"></i> Delete All</a> -->
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
                    <h3 class="box-title">Proxy List</h3>
                    <div class="box-tools">
                        <form action="<?php echo base_url() ?>proxy" method="POST" id="searchList">
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
                        <th>Proxy ID</th>
                        <th>Proxy URL</th>
                        <th>Proxy Port</th>
                        <th>Username</th>
                        <th>Created On</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    <?php
                    if(!empty($proxyRecords))
                    {
                        foreach($proxyRecords as $record)
                        {
                    ?>
                    <tr>
                        <td><?php echo $record['proxy_master_id'] ?></td>
                        <td><?php echo $record['proxy_url'] ?></td>
                        <td><?php echo $record['proxy_port'] ?></td>
                        <td><?php echo $record['username'] ?></td>
                        <td><?php echo date("d-m-Y", strtotime($record['created_date'])) ?></td>
                        <td class="text-center">
                            <a class="btn btn-sm btn-info" href="<?php echo base_url().'proxy/edit/'.$record['proxy_master_id']; ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                            <a class="btn btn-sm btn-danger deleteProxy" href="#" data-proxymasterid="<?php echo $record['proxy_master_id']; ?>" title="Delete"><i class="fa fa-trash"></i></a>
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
    <!-- Modal Import Data-->
    <?php $this->load->helper("form"); ?>
    <form role="form" action="<?php echo base_url() ?>proxy/import" method="post" enctype="multipart/form-data">
        <div class="modal fade" id="show_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Import Proxy List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="file" id="file" name="file"/>
            </div>
            <div class="modal-footer">
                <a href="<?php echo base_url()?>assets/sample/proxy.csv">Sample</a>
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
        jQuery('ul.pagination li a').click(function (e) {
            e.preventDefault();
            var link = jQuery(this).get(0).href;
            var value = link.substring(link.lastIndexOf('/') + 1);
            jQuery("#searchList").attr("action", baseURL + "proxy/" + value);
            jQuery("#searchList").submit();
        });

        //delete proxy start
        jQuery(document).on("click", ".deleteProxy", function(){
            var proxyMasterId = $(this).data("proxymasterid"),
                hitURL = baseURL + "proxy/deleteProxy",
                currentRow = $(this);
            
            var confirmation = confirm("Are you sure to delete this proxy ?");
            
            if(confirmation)
            {
                jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : hitURL,
                data : { proxyMasterId : proxyMasterId } 
                }).done(function(data){
                    currentRow.parents('tr').remove();
                    if(data.status = true) { alert("Proxy successfully deleted"); }
                    else if(data.status = false) { alert("Proxy deletion failed"); }
                    else { alert("Access denied..!"); }
                });
            }
        });
        //delete proxy end

        //delete proxy list start
        jQuery(document).on("click", ".deleteAll", function(){
            var confirmation = confirm("Are you sure want to delete proxy list?");
            
            if(confirmation)
            {
                jQuery.ajax({
                type : "POST",
                dataType : "json",
                url : baseURL + "proxy/deleteAll",
                }).done(function(data){
                    if(data.status = true) { alert("Proxy list successfully deleted"); }
                    else if(data.status = false) { alert("Proxy list deletion failed"); }
                    else { alert("Access denied..!"); }
                });
            }
        });
        //delete proxy list end

        // import data
        jQuery('.import-data').on('click',function(){
            $('#show_modal').modal('show');
        });
    });
</script>
