<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard
        <small>Control panel</small>
      </h1>
    </section>
    
    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-aqua">
                <div class="inner">
                  <h3><?php echo $panelCounts['serverCount']; ?></h3>
                  <p>Total Server</p>
                </div>
                <div class="icon">
                  <i class="ionicons ion-social-buffer"></i>
                </div>
                <a href="<?php echo base_url(); ?>server" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3><?php echo $panelCounts['proxyCount']; ?></h3>
                  <p>Total Proxy</p>
                </div>
                <div class="icon">
                  <i class="ion ion-network"></i>
                </div>
                <a href="<?php echo base_url(); ?>proxy" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-yellow">
                <div class="inner">
                  <h3><?php echo $panelCounts['scheduleCount']; ?></h3>
                  <p>Schedule Data</p>
                </div>
                <div class="icon">
                  <i class="ion ion-calendar"></i>
                </div>
                <a href="<?php echo base_url(); ?>schedule" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-red">
                <div class="inner">
                  <h3><?php echo $panelCounts['clientCount']; ?></h3>
                  <p>Clients</p>
                </div>
                <div class="icon">
                  <i class="ion ion-person-add"></i>
                </div>
                <a href="<?php echo base_url(); ?>client" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
              </div>
            </div><!-- ./col -->
          </div>
    </section>
</div>