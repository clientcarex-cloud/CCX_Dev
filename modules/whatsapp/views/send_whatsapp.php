<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<!-- Optional JS assignment -->


<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin mbot20">Send WhatsApp Message</h4>
			<?php
			/* echo "<pre>";
			print_r($whatsapp_templates);
			echo "</pre>"; */
			?>
        <form action="<?= admin_url('whatsapp/send_template_message') ?>" method="post" id="sendMessageForm">
		<input type="hidden" class="txt_csrfname" id="txt_csrfname" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
              <div class="form-group">
				<label for="template_id">Select Template</label>
				<select name="template_id" id="template_id" class="form-control" required>
					<option value="">-- Select Template --</option>
					<?php foreach ($whatsapp_templates as $template): ?>
						<option value="<?= $template['elementName'] ?>"
								data-category="<?= $template['category'] ?>"
								data-subcategory="<?= $template['subCategory'] ?>"
								data-body="<?= htmlspecialchars($template['body']) ?>"> <!-- Store the message body -->
							<?= $template['elementName'] ?> (<?= $template['category'] ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="form-group">
				<label>Category</label>
				<input type="text" id="template_category" class="form-control" readonly>
			</div>

			<div class="form-group">
				<label>Subcategory</label>
				<input type="text" id="template_subcategory" class="form-control" readonly>
			</div>
			<div class="form-group">
				<label>Message Body</label>
				<textarea id="template_message_body" class="form-control" rows="4" readonly></textarea>
			</div>

              <div class="form-group">
                <label for="mobile_numbers">Mobile Numbers (comma-separated)</label>
				
				<textarea id="mobile_numbers" name="mobile_numbers" class="form-control" rows="4" placeholder="e.g., 919999999999,918888888888" required></textarea>
              </div>

              <div class="form-group" id="variable_fields_container">
					<label for="template_variables">Template Variables (dynamic)</label>
					<!-- Dynamic textboxes will be inserted here -->
				</div>

              <button type="submit" class="btn btn-success">Send Message</button>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11">   </script>
 

<?php init_tail(); ?>
<script>

$(document).ready(function() {
    $('#template_id').on('change', function() {
        // Get the selected template option
        const selectedOption = $(this).find('option:selected');

        // Get category, subcategory, and message body from data attributes
        const category = selectedOption.data('category');
        const subcategory = selectedOption.data('subcategory');
        const messageBody = selectedOption.data('body');
        const customParams = selectedOption.data('customparams'); // Get customParams

        // Set the category and subcategory fields with the values
        $('#template_category').val(category);
        $('#template_subcategory').val(subcategory);

        // Set the message body in the textarea
        $('#template_message_body').val(messageBody);

        // Extract variables from the message body
        extractVariablesFromMessageBody(messageBody);
    });

    // Function to extract variables and generate dynamic input fields
    function extractVariablesFromMessageBody(messageBody) {
		// Regular expression to match placeholders like {{1}}, {{name}}, etc.
		const variableRegex = /{{(.*?)}}/g;
		const matches = [];
		let match;

		// Find all matches in the message body
		while ((match = variableRegex.exec(messageBody)) !== null) {
			matches.push(match[1]); // Extract variable name (e.g., '1', 'name', etc.)
		}

		// Dynamically generate the input fields based on the matches
		generateVariableFields(matches);
	}

	function generateVariableFields(variables) {
		// Clear previous fields
		$('#variable_fields_container').html('');

		// Check if any variables are found
		if (variables.length > 0) {
			variables.forEach(function(variable) {
				// Create input fields for each variable
				const variableInput = `
					<div class="form-group">
						<label for="variable_${variable}">${variable}</label>
						<input type="text" name="template_variables[${variable}]" id="variable_${variable}" class="form-control" placeholder="Enter ${variable}" required>
					</div>
				`;
				$('#variable_fields_container').append(variableInput);
			});
		} else {
			// If no variables, inform the user
			$('#variable_fields_container').html('<p>No variables required for this template.</p>');
		}
	}

});

</script>
</body>
</html>
