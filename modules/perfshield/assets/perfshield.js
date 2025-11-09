'use strict'
if (typeof inactivityTimeout !== 'undefined') {
  let timer = 0

  function set_interval () {
    timer = setInterval(auto_logout, inactivityTimeout)
  }

  function reset_interval () {
    if (timer != 0) {
      clearInterval(timer)
      timer = 0
      timer = setInterval(auto_logout, inactivityTimeout)
    }
  }

  function auto_logout () {
    window.location.href = admin_url + 'authentication/logout'
  }

  set_interval()

  document.body.addEventListener('load', set_interval)
  document.body.addEventListener('mousemove', reset_interval)
  document.body.addEventListener('click', reset_interval)
  document.body.addEventListener('keypress', reset_interval)
  document.body.addEventListener('keyup', reset_interval)
  document.body.addEventListener('keydown', reset_interval)
  document.body.addEventListener('scroll', reset_interval)
}

/**
 * Opens a modal popup for editing an IP address.
 *
 * @param {int} id - The ID of the record to edit.
 * @param {string} ip_address - The IP address to edit.
 */
function openEditIpAddressPopup (id, ip_address) {
  // Show the modal popup for editing an IP address.
  $('#edit_ip_address').modal('show')

  // Set the ID and IP address values for the form inputs.
  $('#edit_ip_address').find('input[name="id"]').val(id)
  $('#edit_ip_address').find('input[name="ip_address"]').val(ip_address)
}

/**
 * Removes an IP address or email from the blacklist.
 *
 * @param {string} category - The category of the item to remove ('ip' or 'email').
 * @param {int} id - The ID of the item to remove.
 */
function removeIpOrEmailFromBlacklist (category, id) {
  // Display a confirmation dialog before proceeding with the removal.
  if (confirm_delete()) {
    // Make an AJAX request to remove the item from the blacklist.
    $.ajax({
      url: `${admin_url}perfshield/removeIpOrEmailFromBlacklist/${category}/${id}`,
      type: 'POST',
      dataType: 'json'
    }).done(function (res) {
      // Display a notification message indicating the result of the removal.
      alert_float(res.type, res.message)

      // Refresh the blacklist IP and email tables to reflect the changes.
      $('.table-blacklist_ip, .table-blacklist_email')
        .DataTable()
        .ajax.reload()
    })
  }
}

function add_ip_validation () {
  $('input.blacklist_ip').each(function () {
    const that = $(this)
    $(this).rules('add', {
      required: true,
      IP4Checker: true,
      remote: {
        url: admin_url + 'perfshield/isUniqueIpOrEmail/ip',
        type: 'post'
      },
      messages: {
        remote: 'Value already in database'
      }
    })
  })
}

function add_email_validation () {
  $('input.blacklist_email').each(function () {
    $(this).rules('add', {
      required: true,
      email: true,
      remote: {
        url: admin_url + 'perfshield/isUniqueIpOrEmail/email',
        type: 'post'
      },
      messages: {
        remote: 'Value already in database'
      }
    })
  })
}

function editStaffExpiryDate (staffID, expiryDate) {
  $('#staffid').val(staffID)
  $('#staffid').selectpicker('refresh')
  $('#expiry_date').val(expiryDate)
  $('#addStaffExpiryForm').find('.add').addClass('hide')
  $('#addStaffExpiryForm').find('.update').removeClass('hide')
}

function removeStaffExpiry (staffID) {
  if (confirm_delete()) {
    requestGetJSON(`${admin_url}perfshield/removeStaffExpiry/${staffID}`).done(
      function (res) {
        alert_float(res.type, res.message)
        $('.table-staff_expiry').DataTable().ajax.reload()
      }
    )
  }
}

$(document).ready(function () {
  function addBruteForceSettings (form) {
    $.ajax({
      url: `${admin_url}perfshield/bruteForceSettings`,
      type: 'POST',
      dataType: 'json',
      data: $(form).serialize()
    }).done(function (res) {
      alert_float(res.type, res.message)
    })
  }

  appValidateForm($('#bruteForceSettingsForm'), {}, addBruteForceSettings);

  function addIpOrEmailToBlacklist (form) {
    const ip_email = $(form).find('input[name="type"]').val()
    $.ajax({
      url: `${admin_url}perfshield/addIpOrEmailToBlacklist`,
      type: 'POST',
      dataType: 'json',
      data: $(form).serialize()
    }).done(function (res) {
      alert_float(res.type, res.message)
      if (res.type == 'success') {
        $(`input[name="blacklist_${ip_email}[0]"]`).val('')
        $(`.blacklist_${ip_email}_row`)
          .not(`#blacklist_${ip_email}_row_0`)
          .remove()
        $(`.table-blacklist_${ip_email}`).DataTable().ajax.reload()
      }
    })
  }

  appValidateForm($('#blacklist_ip_form'), {}, addIpOrEmailToBlacklist)

  appValidateForm($('#blacklist_email_form'), {}, addIpOrEmailToBlacklist)

  function updateIPAddress (form) {
    $.ajax({
      url: `${admin_url}perfshield/updateIPAddress`,
      type: 'POST',
      dataType: 'json',
      data: $(form).serialize()
    }).done(function (res) {
      $('#edit_ip_address').modal('hide')
      alert_float(res.type, res.message)
      $('.table-blacklist_ip').DataTable().ajax.reload()
    })
  }

  appValidateForm(
    $('#edit_ip_address_form'),
    {
      edit_ip_address: 'required'
    },
    updateIPAddress
  )

  function addStaffExpiryForm (form) {
    $.ajax({
      url: `${admin_url}perfshield/addStaffExpiry`,
      type: 'POST',
      dataType: 'json',
      data: $(form).serialize()
    }).done(function (res) {
      $('#addStaffExpiryForm').find('.add').removeClass('hide')
      $('#addStaffExpiryForm').find('.update').addClass('hide')

      alert_float(res.type, res.message)
      $('.table-staff_expiry').DataTable().ajax.reload()

      $('#staffid').val('')
      $('#staffid').selectpicker('refresh')
      $('#expiry_date').val('')
    })
  }

  appValidateForm(
    $('#addStaffExpiryForm'),
    {
      staffid: 'required',
      expiry_date: 'required'
    },
    addStaffExpiryForm
  )

  function saveSingleSessionSettings (form) {
    $.ajax({
      url: `${admin_url}perfshield/saveSingleSessionSettings`,
      type: 'POST',
      dataType: 'json',
      data: $(form).serialize()
    }).done(function (res) {
      alert_float(res.type, res.message)
    })
  }

  appValidateForm(
    $('#single-session-form'),
    {
      prevent_user_from_login_more_than_once: 'required'
    },
    saveSingleSessionSettings
  )

  $('body').on('click', '.clear_log', function (event) {
    if (confirm_delete()) {
      $.ajax({
        url: `${admin_url}perfshield/clearLogs`,
        type: 'POST',
        dataType: 'json'
      }).done(function (res) {
        alert_float(res.type, res.message)
        $('.table-perfshield_logs').DataTable().ajax.reload()
      })
    }
  })
})
