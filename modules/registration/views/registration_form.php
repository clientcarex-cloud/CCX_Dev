<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= _l('registration_page') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link
    rel="stylesheet"
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
  >
  <style>
    .section { display: none; }
    .centered-container {
      max-width: 100%;
      padding: 20px;
    }
    @media (min-width: 576px) {
      .centered-container {
        max-width: 540px;
        margin: 0 auto;
      }
    }
    .header-logo {
      max-width: 150px;
      height: auto;
      display: block;
      margin: 0 auto;
    }
    .header-title {
      text-align: center;
      font-size: 24px;
      margin-top: 20px;
    }
  </style>
</head>
<body>
<div class="container-fluid">

  <!-- Header with Centered Logo and Heading -->
  <div class="text-center py-1">
    <?php $logo = get_admin_header_logo_url(); ?>
    <img src="<?php echo $logo;?>" alt="Logo" class="header-logo">
    <h1 class="header-title"><?= _l('registration_page') ?></h1>
  </div>
  <div class="container centered-container">

    <!-- Mobile Form -->
    <div class="card p-4 shadow-sm section" id="mobileSection">
      <h5 class="mb-3"><?= _l('enter_mobile_number') ?></h5>
      <form id="mobileForm">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="tel" class="form-control mb-3" id="mobile" name="mobile" required placeholder="<?= _l('mobile_number') ?>">
        <button type="submit" class="btn btn-primary btn-block"><?= _l('continue') ?></button>
      </form>
    </div>

    <!-- OTP Form -->
    <div class="card p-4 shadow-sm section" id="otpSection">
      <h5 class="mb-3"><?= _l('verify_otp') ?></h5>
      <form id="otpForm">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="otpMobile">
        <input type="text" class="form-control mb-3" id="otp" required placeholder="<?= _l('enter_otp') ?>">
        <button type="submit" class="btn btn-success btn-block"><?= _l('verify') ?></button>
      </form>
    </div>

    <!-- Registration Form -->
    <div class="card p-4 shadow-sm section" id="registerSection">
      <h5 class="mb-3"><?= _l('new_client_registration') ?></h5>
      <form id="registerForm" enctype="multipart/form-data">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="registerMobile" name="mobile">
        <input type="text" class="form-control mb-3" name="name" placeholder="<?= _l('full_name') ?>" required>
        <input type="date" class="form-control mb-3" name="dob" placeholder="<?= _l('date_of_birth') ?>" required>
        <input type="email" class="form-control mb-3" name="email" placeholder="<?= _l('email') ?>">
        <textarea class="form-control mb-3" name="address" placeholder="<?= _l('address') ?>" rows="3"></textarea>
        <button type="submit" class="btn btn-primary btn-block"><?= _l('register') ?></button>
      </form>
    </div>

    <!-- Menu Section -->
    <div class="card p-4 shadow-sm section" id="menuSection">
      <h5><?= _l('welcome') ?>, <span id="clientName"><?= isset($client['company']) ? $client['company'] : '' ?></span></h5>
      <div class="btn-group-vertical w-100 mt-3">
        <button class="btn btn-outline-primary" id="btnBook"><?= _l('book_appointment') ?></button>
        <button class="btn btn-outline-secondary"><?= _l('feedback') ?></button>
        <button class="btn btn-outline-success" id="btnPayments"><?= _l('payments') ?></button>
        <button class="btn btn-outline-warning"><?= _l('refer') ?></button>
        <button class="btn btn-outline-danger" id="Logout"><?= _l('logout') ?></button>
      </div>
    </div>

    <!-- Appointment Form -->
    <div class="card p-4 shadow-sm section" id="appointmentSection">
      <h5 class="mb-3"><?= _l('book_appointment') ?></h5>
      <form id="appointmentForm">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="userid" value="<?= $this->session->userdata('user_id'); ?>">
        <div class="form-group">
          <label for="doctor_id"><?= _l('doctor') ?></label>
          <select class="form-control" name="doctor_id" required>
            <option value="1"><?= _l('select_doctor') ?></option>
            <?php foreach ($doctors as $doctor): ?>
              <option value="<?= $doctor['staffid'] ?>"><?= $doctor['firstname'].' '.$doctor['lastname'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="treatment_id"><?= _l('treatment') ?></label>
          <select class="form-control" name="treatment_id" required>
            <option value="1"><?= _l('select_treatment') ?></option>
            <?php foreach ($treatments as $treatment): ?>
              <option value="<?= $treatment['treatment_id'] ?>"><?= $treatment['treatment_name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="appointment_date"><?= _l('select_date') ?></label>
          <input type="datetime-local" class="form-control" name="appointment_date" required>
        </div>
        <button type="submit" class="btn btn-success btn-block"><?= _l('confirm_appointment') ?></button>
      </form>
    </div>

    <!-- Payment Section -->
    <div class="card p-4 shadow-sm section" id="paymentSection">
      <h5 class="mb-3"><?= _l('invoice_details') ?></h5>

      <?php if (isset($invoice)): ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead class="thead-dark">
              <tr>
                <th><?= _l('invoice_number') ?></th>
                <th><?= _l('date') ?></th>
                <th><?= _l('due_date') ?></th>
                <th><?= _l('total') ?></th>
                <th><?= _l('status') ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><?= $invoice->formatted_number ?></td>
                <td><?= date('d-m-Y', strtotime($invoice->date)) ?></td>
                <td><?= date('d-m-Y', strtotime($invoice->duedate)) ?></td>
                <td>₹<?= number_format($invoice->total, 2) ?></td>
                <td><?= format_invoice_status_custom($invoice->status); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-muted"><?= _l('no_invoice_found') ?></p>
      <?php endif; ?>

      <button class="btn btn-secondary btn-block mt-3" id="btnBackFromPayment"><?= _l('back') ?></button>
    </div>

  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
  const csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
  const csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

  $(function () {
    const status = '<?= $status ?>';
    const clientName = '<?= isset($client["company"]) ? $client["company"] : "" ?>';

    if (status === 'verified') {
      $('#menuSection').show();
      $('#clientName').text(clientName);
    } else if (status === 'register') {
      $('#registerSection').show();
      $('#registerMobile').val('<?= $this->session->userdata("register_mobile") ?>');
    } else if (status === 'otp_pending') {
      $('#otpSection').show();
      $('#otpMobile').val('<?= $this->session->userdata("otp_mobile") ?>');
    } else {
      $('#mobileSection').show();
    }

    $('#mobileForm').on('submit', function (e) {
      e.preventDefault();
      const data = {
        mobile: $('#mobile').val()
      };
      data[csrfName] = csrfHash;

      $.post('<?= base_url('registration/check_mobile') ?>', data, function (response) {
        const res = JSON.parse(response);
        if (res.status === 'otp') {
          $('#mobileSection').hide();
          $('#otpMobile').val(res.mobile);
          $('#otpSection').show();
        } else {
          $('#mobileSection').hide();
          $('#registerMobile').val(res.mobile);
          $('#registerSection').show();
        }
      });
    });

    $('#otpForm').on('submit', function (e) {
      e.preventDefault();
      const data = {
        mobile: $('#otpMobile').val(),
        otp: $('#otp').val()
      };
      data[csrfName] = csrfHash;

      $.post('<?= base_url('registration/verify_otp') ?>', data, function (response) {
        const res = JSON.parse(response);
        if (res.status === 'success') {
          $('#otpSection').hide();
          $('#clientName').text(res.name);
          $('#menuSection').show();
          location.reload();
        } else {
          alert('Invalid OTP');
        }
      });
    });

    $('#registerForm').on('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append(csrfName, csrfHash);

      $.ajax({
        url: '<?= base_url('registration/save_new_client') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
          const res = JSON.parse(response);
          if (res.status === 'success') {
            $('#registerSection').hide();
            $('#clientName').text(res.name);
            $('#menuSection').show();
            location.reload();
          } else {
            alert('Registration failed.');
          }
        }
      });
    });

    $('#btnBook').on('click', function () {
      $('.section').hide();
      $('#appointmentSection').show();
    });

    $('#appointmentForm').on('submit', function (e) {
      e.preventDefault();
      const data = $(this).serialize();
      $.post('<?= base_url('registration/save_appointment') ?>', data, function (response) {
        const res = JSON.parse(response);
        if (res.status === 'success') {
          alert('Appointment booked.');
          $('.section').hide();
          $('#menuSection').show();
          location.reload();
        } else {
          alert('Error booking appointment.');
        }
      });
    });
    
    $('#Logout').click(function () {
      $.ajax({
        url: '<?= base_url("registration/logout") ?>',
        type: 'POST',
        data: { [csrfName]: csrfHash },
        success: function (response) {
          window.location.href = '<?= base_url("registration") ?>';
        },
        error: function (xhr) {
          alert('Logout failed. Please try again.');
        }
      });
    });
    
    $('.btn-outline-success').on('click', function () {
      $('.section').hide();
      $('#paymentSection').show();
    });

    $('#btnBackFromPayment').on('click', function () {
      $('.section').hide();
      $('#menuSection').show();
    });

    $('#btnPayments').on('click', function () {
      $('.section').hide();
      $('#paymentSection').show();
    });

  });
</script>
</body>
</html>
