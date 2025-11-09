<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div role="tabpanel" class="tab-pane" id="message_log">
    
    
    <div>
        <!-- Hidden Form -->
        

        <br>

        <!-- Table -->
        <?php if (staff_can('view_call_log', 'customers')) : ?>
			<?= render_datatable([
				_l('s_no'),
				_l('status'),
				_l('message_type'),
				_l('message'),
				_l('response'),
				_l('datetime'),
			], 'message-log-table'); ?>

		<?php endif; ?>

    </div>
</div>

<script>
function toggleCallLogForm() {
    const form = document.getElementById('call-log-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

$(function() {
    initDataTable('.table-message-log-table', admin_url + 'lead_call_log/message_log_table/' + <?= $lead->id ?>, undefined, undefined, '', [0, 'desc']);
});

</script>

<script>
$(document).ready(function() {
    // List of trigger responses passed from PHP
    const triggerResponses = <?= json_encode($enabled_responses) ?>;
    function checkCallReceivedTrigger() {
        const selectedText = $('.patient_response_id option:selected').text().trim();
        if (triggerResponses.includes(selectedText)) {
            $('#call_received').show();
        } else {
            $('#call_received').hide();
        }
    }

    // Check on select change
    $('.patient_response_id').on('change', checkCallReceivedTrigger);

    // Also check on page load in case it's pre-selected
    checkCallReceivedTrigger();
});
</script>

<script>
$(document).ready(function () {
    $('#itemSelect').on('changed.bs.select change', function () {
        const selectedRate = parseFloat($(this).find('option:selected').data('rate')) || 0;
        $('#paymentAmount').attr('max', selectedRate);
        $('#paymentAmount').trigger('input'); // recheck if value already exists
    });

    $('#paymentAmount').on('input', function () {
        const rate = parseFloat($('#itemSelect').find('option:selected').data('rate')) || 0;
        const val = parseFloat($(this).val()) || 0;

        if (val > rate) {
            $('#amountError').show();
            $(this).addClass('is-invalid');
        } else {
            $('#amountError').hide();
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
