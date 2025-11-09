<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = ['template_id','template_name','template_body','created_at'];

$sIndexColumn = 'id';
$sTable       = db_prefix().'_templates';

$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

usort($rResult, function ($a, $b) {
    return $a['id'] - $b['id'];
});



foreach ($rResult as $aRow) {
    $row = [];
    
    
    $row[]   = $aRow['template_id'];
    $row[]   = $aRow['template_name'];
    $row[]   = $aRow['template_body'];
    $row[]   = $aRow['created_at'];
    
    $template_body = trim($aRow['template_body']);
   
    $href = "open_modal('".$aRow['id']."','".$aRow['template_id']."','".$aRow['template_name']."')";
    
    $delete = "delete_template('".admin_url().'template/templates/delete_templates/'.$aRow['id']."')";
    
    $row[] = '<a onclick="'.$delete.'" class="btn btn-danger btn-icon">
    Delete</a>
        ';
        
        //After</a> of Delete//<a onclick="'.$href.'" class="btn btn-warning  btn-icon">Edit</a>



    $output['aaData'][] = $row;
}
