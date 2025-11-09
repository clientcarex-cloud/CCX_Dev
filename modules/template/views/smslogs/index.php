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
    
        <div class="clearfix"></div>
        <?php render_datatable(array(
        'Id',
        'Template ID',
        'Message',
        'Status',
        'Numbers',
        'Credit',
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
