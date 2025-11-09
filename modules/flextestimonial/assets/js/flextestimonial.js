"use strict";

function flextestimonail_accordion_update_icon() {
  $(".panel-collapse").on("show.bs.collapse", function () {
    $(this)
      .closest(".panel")
      .find(".accordion-arrow")
      .removeClass("fa-chevron-down")
      .addClass("fa-chevron-up");
    flextestimonial_update_preview($(this).attr("id"));
  });

  $(".panel-collapse").on("hide.bs.collapse", function () {
    $(this)
      .closest(".panel")
      .find(".accordion-arrow")
      .removeClass("fa-chevron-up")
      .addClass("fa-chevron-down");
  });
}

function flextestimonial_update_preview(panel_id) {
  const ajaxUrl = $("#flextestimonial-ajax-url").val();
  const slug = $("#flextestimonial-slug").val();
  //make a request to get the right preview and update the preview section
  $.ajax({
    url: ajaxUrl,
    type: "POST",
    data: {
      action: "get_preview",
      panel_id: panel_id,
      slug: slug,
    },
    success: function (response) {
      const data = JSON.parse(response);
      if (data.success) {
        $(".flextestimonial-display-content-section").html(data.html);
      }
    },
  });
}

function flextestimonial_copyToClipboard(text, msg) {

  //if text is an id, get the text from the element
  if(text.startsWith("#")) {
    text = $(text).text(); //this the embed code
  }
  navigator.clipboard
    .writeText(text)
    .then(function () {
      alert_float("success", msg);
    })
    .catch(function (err) {});
}

function flextestimonial_init() {
  flextestimonail_accordion_update_icon();
  //delete testimonial confirmation
  $(document).on("click", ".btn-flextestimonial-delete", function () {
    var r = confirm(app.lang.confirm_action_prompt);
    if (r == false) {
      return false;
    }
  });

  // Handle all form input changes including color picker
  $(document).on(
    "change",
    '.flex-testimonial-left-column input[type="color"], .flex-testimonial-left-column input[type="radio"], .flex-testimonial-left-column input[type="checkbox"]',
    function () {
      flextestimonial_save_changes(this);
    }
  );

  //onkeyup for text inputs and textareas
  $(document).on(
    "keyup",
    '.flex-testimonial-left-column input[type="text"], .flex-testimonial-left-column textarea',
    function () {
      flextestimonial_save_changes(this);
    }
  );

  $("#flextestimonial-save-changes").on("click", function () {
    flextestimonial_save_changes(this);
    return false;
  });

  function flextestimonial_save_changes(obj) {
    var form = $(obj).closest("form");
    var formData = form.serialize();
    $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: formData,
      success: function (response) {
        const data = JSON.parse(response);
        if (data.success) {
          //update the preview sections
          $(".testimonial-preview-section").html(data.html);
          // Use debounced alert instead of direct alert_float
          debouncedAlert("success", data.message);
        }
      },
    });
    return false;
  }

  $(document).on("click", ".flex-testimonial-featured-label", function () {
    var id = $(this).data("id");
    var featured = $(this).find('input').is(":checked") ? 1 : 0;
    $.ajax({
      url: $("#flextestimonial-ajax-url").val(),
      type: "POST",
      data: {
        action: "update_response_featured",
        id: id,
        featured: featured,
      },
      success: function (response) {
        const data = JSON.parse(response);
        if (data.success) {
          alert_float("success", data.message);
        }
      },
    });
  });

  //rating star
  $(document).on("click", ".flex-testimonial-rating-star", function () {
    $('input[name="rating"]').attr("checked", false);
    $(".flex-testimonial-rating-star").removeClass("active");
    $(this).find('input').attr("checked", true);
    for (var i = 0; i < $(this).data("rating"); i++) {
      $(".flex-testimonial-rating-star").eq(i).addClass("active");
    }
    return false;
  });

  //video cta
  $(document).on("click", ".testimonial-preview-section #flextestimonial-video-cta", function () {
    flextestimonial_update_preview("collapsevideo_testimonial");
  });

  //text cta
  $(document).on("click", ".testimonial-preview-section #flextestimonial-text-cta", function () {
    flextestimonial_update_preview("collapseresponse_prompt");
  });

  //back icon
  $(document).on("click", ".flex-testimonial-back-icon", function () {
    flextestimonial_update_preview("collapsewelcome_page");
  });
}

// Add debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Create debounced version of alert_float
const debouncedAlert = debounce((type, message) => {
    alert_float(type, message);
}, 1000); // 1 second delay

$(function () {
  flextestimonial_init();
});
