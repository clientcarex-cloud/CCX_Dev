            <?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
            <?php init_head(); ?>
			<!-- Optional JS assignment -->
			<script>
				var smsbalance = <?= json_encode($smsbalance); ?>;
				//console.log("SMS Wallet Balance:", smsbalance);
			</script>
             <style>
            .swal2-popup { font-size: 1.6rem !important; }
        </style>
            <div id="wrapper">
            <div class="content">
            <div class="row">
            <div class="col-md-12">
            <div class="panel_s">
            <div class="panel-body">
            <form action="<?php echo admin_url().'template/sms/send_sms';?>" method="POST">
            <input type="hidden" class="txt_csrfname" id="txt_csrfname" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
       
            <div class="form-group select-placeholder">
            <label for="template_id">Select Template </label><br>
            <select required name="template_id" id="template_id" class="form-control selectpicker" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-width="30%" onchange="changes(this.value)">
            <?php 
            $this->db->select('*');
            $data =  $this->db->get(db_prefix().'_templates')->result_array();
            foreach($data as $member){ ?>
            <option <?php //if($member['template_id']==$rec['roles']){echo 'selected';} ?> value="<?php echo $member['template_id']; ?>"><?php echo  $member['template_name'] ; ?>
            </option>
            <?php } ?>
            </select>
            </div>
             <label for="phonenumber">Phone Numbers </label> <span style="font-size: 9px;">(Multiple Number Seprated by Comma)</span>
            <textarea type="number" required rows="5" id="phonenumber" name="phonenumber" class="form-control" onkeyup="count_values(), validateNumber()"></textarea>
            <span name="num_count" style="font-size: 12px; text-align: right; float: right;">Total Numbers: <span id="count"></span></span>  
            <br>
              <label for="message"> Message</label>
<textarea id="message" required rows="5" name="message" class="form-control" onkeyup="count_chars()"></textarea>
<span name="num_count" style="font-size: 12px; text-align: right; float: right;">Characters Count: <span id="char_count"></span></span>
             <br>
             <br>
             
             <div class="alert alert-warning">
                    Enter your text value within the Brackets {} by Replacing #var#<br>
                    eg:- {Your Message}
             </div>
    

            <input type="submit" id='submit_button'  value="Send" class="btn btn-success pull-right">
            <button type="button" class="btn btn-warning" onclick="removeDuplicates()">Remove Duplicates</button>

            </form>		
            
            </div>
            </div>
            </div>
            </div>
            </div>
            </div>
             <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11">   </script>
             <script>
             function validateNumber(){
                 //Check SMS Balance
                 var phonenumber = document.getElementById("phonenumber").value;
                 var numbers = phonenumber.split(",");
                 var total_numbers = numbers.length;
                 console.log(total_numbers);
                 //
                 
                 var message = document.getElementById("message");
                 var count = message.value.length;
                 var finalresult = count * total_numbers;
                 var creditresult = Math.ceil(finalresult / 160);
                 
                 if(smsbalance <= creditresult){
    document.getElementById('submit_button').style.display = 'none';
    Swal.fire({
      title: "Warning!",
      text: "You don't have enough balance",
      icon: "warning",
      width: "500px",
      height: "200px",
      showCancelButton: false,
      confirmButtonText: "OK"
    });
  }
                 
                 
                 //
    var phonenumber = document.getElementById("phonenumber").value;
    var phonenumber_regex = /^[0-9,\s]+$/;
    if(!phonenumber_regex.test(phonenumber)){
        alert("Please enter valid phone numbers separated by comma");
        document.getElementById("phonenumber").value = "";
    }
}

    function count_values() {
        var input = document.getElementById("phonenumber").value;
        var values = input.split(",");
        var count = values.length;
        document.getElementById("count").innerHTML = count;
    }
    function count_chars() {
        var input = document.getElementById("message").value;
        var count = input.replace(/{|}/g, "").length;
        document.getElementById("char_count").innerHTML = count;
    }
    function removeDuplicates() {
    // Get the phone number input field
    var input = document.getElementById("phonenumber");
    // Split the input into an array of values
    var values = input.value.split(",");
    // Use the Set object to remove duplicate values
    var uniqueValues = [...new Set(values)];
    // Join the array of unique values back into a string
    input.value = uniqueValues.join(",");
    
    count_values();
}
</script>
        <script>
         <?php if($_SESSION['smssent']==1){?>
        Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'SMS Sent !',
        showConfirmButton: false,
        timer: 1500
        })
        <?php  }
        if($_SESSION['smsnotsent']==1){ ?>
        Swal.fire({
        position: 'top-end',
        icon: 'warning',
        title: 'SMS Not Sent !',
        showConfirmButton: false,
        timer: 1500
        })
        <?php  }
        $this->session->unset_userdata('smssent');
        $this->session->unset_userdata('smsnotsent');
        ?>
        </script>
            <?php init_tail(); ?>
            </body>
            </html>
            
            
    <script>
    
    var id=$('#template_id').val();
    $.ajax({
    url: "<?php echo admin_url().'template/sms/ajax_data';?>",
    type: "POST",
    data: {id : id},
    dataType: "json",
    success: function(resp){
    $('#message').html(resp.template_body);
    
    
    }
    });
    
    function changes(a){
    //alert(a);
    $.ajax({
    url: "<?php echo admin_url().'template/sms/ajax_data';?>",
    type: "POST",
    data: {id : a},
    dataType: "json",
    success: function(resp){
    $('#message').html(resp.template_body);
    
    
    }
    });
    }
    
    </script>
