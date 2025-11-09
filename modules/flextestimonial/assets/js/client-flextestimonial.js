"use strict";
function flextestimonial_reset_layout() {
  //reset the body classs
  if ($(".flextestimonial-full-page-display").length > 0) {
    $("#wrapper")
      .find(".container")
      .removeClass("container")
      .addClass("container-fluid");
  }
}

function initMasonry() {
    var grid = document.querySelector('.tw-grid-masonry');
    //if not grid is not found, return
    if (!grid) {
      return;
    }
    var masonry = new Masonry(grid, {
        itemSelector: '.tw-grid-item',
        columnWidth: '.tw-grid-sizer',
        percentPosition: true,
        gutter: 24,
        initLayout: true,
        fitWidth: true
    });

    // Add a sizer element
    var sizer = document.createElement('div');
    sizer.className = 'tw-grid-sizer';
    grid.insertBefore(sizer, grid.firstChild);

    // Set the sizer width based on viewport
    function updateSizerWidth() {
        var containerWidth = grid.offsetWidth;
        var columns = window.innerWidth >= 1024 ? 3 : (window.innerWidth >= 768 ? 3 : 1);
        var gutterTotal = (columns - 1) * 24; // 24px gutter
        var itemWidth = (containerWidth - gutterTotal) / columns;
        sizer.style.width = itemWidth + 'px';
        document.querySelectorAll('.tw-grid-item').forEach(function(item) {
            item.style.width = itemWidth + 'px';
        });
        masonry.layout();
    }

    // Initial setup
    updateSizerWidth();
    
    // Update on window resize
    window.addEventListener('resize', updateSizerWidth);

    // Re-layout Masonry when images are loaded
    imagesLoaded(grid).on('progress', function() {
        masonry.layout();
    });
}
$(document).on("click", "#flextestimonial-video-cta", function () {
  //hide other sections and show the video section
  //welcome section
  $(".flextestimonial-display-content-section-welcome").hide();
  $(".flextestimonial-display-content-section-text-response").hide();
  $(".flextestimonial-display-content-section-customer-info").hide();
  $(".flextestimonial-display-content-section-video-response").show();
});

$(document).on("click", "#flextestimonial-text-cta", function () {
  //hide other sections and show the text section
  //welcome section
  
  $(".flextestimonial-display-content-section-welcome").hide();
  $(".flextestimonial-display-content-section-video-response").hide();
  $(".flextestimonial-display-content-section-customer-info").hide();
  $(".flextestimonial-display-content-section-text-response").show();
});

$(document).on("click", ".flex-testimonial-back-icon", function () {
  //hide all sections and show the welcome section
  $(".flextestimonial-display-content-section-welcome").show();
  $(".flextestimonial-display-content-section-text-response").hide();
  $(".flextestimonial-display-content-section-video-response").hide();
  $(".flextestimonial-display-content-section-customer-info").hide();
});

$(document).on("click", "#flextestimonial-submit-text-response", function () {
  //hide the text section and show the customer info section
  const msg = $(this).data("msg");
  //if text and images files are empty show an alert
  if ($('textarea[name="text_response"]').val() == "") {
    alert_float("info", msg);
    //shake the textarea temporarily
    $('textarea[name="text_response"]').addClass("shake");
    setTimeout(function () {
      $('textarea[name="text_response"]').removeClass("shake");
    }, 1000);
    return false;
  }
  $(".flextestimonial-display-content-section-text-response").hide();
  $(".flextestimonial-display-content-section-customer-info").show();
});
//submit the video response
$(document).on("click", "#flextestimonial-submit-video-response", function () {
  const msg = $(this).data("msg");
  //Validation, check if the video is uploaded
  if (
    !window.flextestimonialVideoData &&
    $('input[name="video_file"]').val() == ""
  ) {
    alert_float("info", msg);
    return false;
  }

  // Stop video playback
  const videoPreview = document.getElementById("video-preview");
  if (videoPreview) {
    videoPreview.pause();
    videoPreview.currentTime = 0;
  }

  //hide the video section and show the customer info section
  $(".flextestimonial-display-content-section-video-response").hide();
  $(".flextestimonial-display-content-section-customer-info").show();
});

function flextestimonial_copyToClipboard(text, msg) {
  navigator.clipboard
    .writeText(text)
    .then(function () {
      alert_float("success", msg);
    })
    .catch(function (err) {});
}
//submit the customer info
$(document).on("click", "#flextestimonial-submit-customer-info", function () {
  //this will submit the form
  const obj = $(this);
  $(obj).prop("disabled", true);
  var form = $(obj).closest("form");

  // Create a new FormData object
  const formData = new FormData(form[0]);

  // If we have video data, append it to the form data
  if (window.flextestimonialVideoData) {
    for (let pair of window.flextestimonialVideoData.entries()) {
      formData.append(pair[0], pair[1]);
    }
  }

  $.ajax({
    url: $(form).attr("action"),
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    success: function (response) {
      console.log(response);
      const r = JSON.parse(response);
      $(obj).prop("disabled", false);
      if (r.status === "success") {
        $(".flextestimonial-display-content-section").html(r.html);
      }
      if (r.status == "error") {
        alert_float("info", r.message);
        if (r.restart) {
          $(".flextestimonial-display-content-section-welcome").show();
          $(".flextestimonial-display-content-section-text-response").hide();
          $(".flextestimonial-display-content-section-video-response").hide();
          $(".flextestimonial-display-content-section-customer-info").hide();
        }
      }
     
    },
    error: function (xhr, status, error) {
      console.log(xhr);
      console.log(status);
      console.log(error);
    },
  });
});

//load more responses
$(document).on("click", "#flextestimonial-wall-of-love-load-more-button", function () {
  const obj = $(this);
  const offset = obj.data("offset");
  const limit = obj.data("limit");
  const loadmore = obj.data("loadmore");
  const loading = obj.data("loading");
  obj.html(loading);
  $.ajax({
    url: $(obj).data("url"),
    type: "POST",
    data: {
      offset: offset,
      limit: limit,
    },
    success: function (response) {
      const r = JSON.parse(response);
      obj.html(loadmore);
      if (r.status === "success") {
        if (r.html == "") {
          obj.hide();
        } else {
          $(".flextestimonial-wall-of-love-responses .tw-grid-masonry").append(r.html);
          obj.data("offset", offset + limit);
          initMasonry();
        }
      }
    },
  });
});
//rating star
$(document).on("click", "form .flex-testimonial-rating-star", function () {
  const obj = $(this);
  $(".flex-testimonial-rating-star input").attr("checked", false);
  $(".flex-testimonial-rating-star").removeClass("active");
  $(this).find("input").attr("checked", true);
  for (var i = 0; i < $(this).data("rating"); i++) {
    //if preview
    if (
      $(obj).closest(".flextestimonial-display-content-section-text-response")
        .length > 0
    ) {
      $(obj)
        .closest(".flextestimonial-display-content-section-text-response")
        .find(".flex-testimonial-rating-star")
        .eq(i)
        .addClass("active");
    } else if (
      $(obj).closest(".flextestimonial-display-content-section-video-response")
        .length > 0
    ) {
      $(obj)
        .closest(".flextestimonial-display-content-section-video-response")
        .find(".flex-testimonial-rating-star")
        .eq(i)
        .addClass("active");
    } else {
      $(".flex-testimonial-rating-star").eq(i).addClass("active");
    }
  }
  return false;
});
//when the upload input changes, the sibling label has a span, update the span with the number of files uploaded
$(document).on("change", ".flex-testimonial-upload-input", function () {
  const obj = $(this);
  const label = obj.siblings("label");
  const files = obj[0].files;

  if (files && files.length > 0) {
    // If there are files, update the label text
    label.find("span").text("(" + files.length + ")");
  } else {
    // If no files, reset the label text
    label.find("span").text("");
  }
});

//when the input user_photo changes, update the preview .flex-testimonial-user-photo img
$(document).on("change", "input[name='user_photo']", function () {
  const obj = $(this);
  const preview = $(".flex-testimonial-user-photo");
  const file = obj[0].files[0];
  const reader = new FileReader();
  reader.onload = function (e) {
    preview.attr("src", e.target.result);
  };
  reader.readAsDataURL(file);
});

$(function () {
  flextestimonial_reset_layout();
  initMasonry();
});
