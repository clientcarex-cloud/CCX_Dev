        <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
        <?php init_head(); ?>
        <style>
            .swal2-popup { font-size: 1.6rem !important; }
        </style>
        <div id="wrapper">
        <div class="content">
        <div class="row">
        <div class="col-md-12">
        <div class="panel_s">
        <div class="panel-body">
            <?php 
            $this->db->select('*');
            $this->db->order_by('id','desc');
            $this->db->limit(1);
            $data =  $this->db->get(db_prefix().'_smscredits')->row_array();
            ?>
            
            <div class="col-md-12">
<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="tw-w-5 tw-h-5 tw-text-neutral-500 tw-mr-1.5">
<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
 </svg>
<span>
Active SMS Package </span>
</h4>
</div>
<div class="col-md-2 col-xs-6 tw-border-r tw-border-solid tw-border-neutral-300 tw-flex tw-items-center">
<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
<?php echo $data['pcredit'] - $data['ccredit']; ?></span>
<span class="text-dark">Used Credits</span>
</div>
<div class="col-md-2 col-xs-6 tw-border-r tw-border-solid tw-border-neutral-300 tw-flex tw-items-center">
<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
<?php echo $data['ccredit']; ?> </span>
<span class="text-dark">Left Credits</span>
</div>
<div class="col-md-2 col-xs-6 tw-flex tw-items-center">
<span class="tw-font-semibold tw-mr-3 rtl:tw-ml-3 tw-text-lg">
<?php echo $data['id']; ?> </span>
<span class="text-dark">Package ID</span>
</div>
            <span><h1>&nbsp;</h1></span>
    
        <div class="clearfix"></div>
        <?php render_datatable(array(
        'Id',
        'SMS Package',
        'Purchased Credits',
        'Left Credits',
        'Description',
        'Date & Time',
        ),'template'); ?> 
        
        </div>
        
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>
        
        <?php init_tail(); ?>
            
        }
        
        
        <script>
     
        $(function(){
        initDataTable('.table-template', window.location.href, [1], [1]);
        });
        
        </script>
        </body>
        </html>
