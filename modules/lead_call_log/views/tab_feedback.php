<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div role="tabpanel" class="tab-pane" id="feedback">
    
    
    <div>
        <!-- Hidden Form -->
        

        <br>

        <!-- Table -->
        <?= render_datatable([
			_l('feedback_title'),
			_l('feedback_description'),
			_l('view'),
		], 'feedback-table'); ?>

    </div>
</div>

<script>

$(function() {
    initDataTable('.table-feedback-table', admin_url + 'lead_call_log/get_feedback_table/' + <?= $lead->id ?>, undefined, undefined, '', [0, 'desc']);
	
});

</script>
<script>
$(document).on('click', '.view-feedback', function () {
    const $icon = $(this);
    const id = $icon.data('title');
    const $row = $icon.closest('tr');
	
	// JS unserialize function (very basic)
function jsUnserialize(str) {
    try {
        // only handles arrays like: a:1:{i:0;s:36:"filename.jpg";}
        let match = str.match(/s:\d+:"([^"]+)"/g);
        if (!match) return [];

        return match.map(m => m.match(/"([^"]+)"/)[1]);
    } catch (e) {
        return [];
    }
}

// Convert base64 + unserialize like PHP
function flextestimonial_perfect_unserialize_js(encoded) {
    try {
        const decoded = atob(encoded);
        return jsUnserialize(decoded);
    } catch (e) {
        return jsUnserialize(encoded);
    }
}

// Build full media URL
function flextestimonial_media_url(url) {
    if (url.startsWith('http')) return url;
    return `${site_url}uploads/flextestimonial/${url}`;
}
function renderStars(rating) {
    rating = parseInt(rating) || 0;

    // Determine star color based on rating
    let starColor = '';
    if (rating <= 2) {
        starColor = 'red';
    } else if (rating === 3) {
        starColor = 'orange';
    } else if (rating >= 4) {
        starColor = 'green';
    }

    let stars = '';
    for (let i = 1; i <= 5; i++) {
        const isActive = i <= rating ? 'active' : '';
        const iconType = i <= rating ? 'fas' : 'far'; // filled or outline star
        const iconColor = i <= rating ? starColor : '#ccc'; // filled = dynamic, empty = gray

        stars += `
            <a href="#" class="flex-testimonial-rating-star ${isActive}" data-rating="${i}">
                <i class="${iconType} fa-star" style="color: ${iconColor};"></i>
                <input type="radio" name="text_rating" value="${i}" class="flex-testimonial-rating-star-input" style="display: none;">
            </a>
        `;
    }

    return `<div class="flex-testimonial-rating">${stars}</div>`;
}





    // Remove existing .response-row if already present
    $('.response-row').remove();

    // Insert new row after clicked row
    const $newRow = $(`
        <tr class="response-row">
            <td colspan="${$row.find('td').length}">
                <div class="response-content">
                    <div class="text-center text-muted p-2">Loading responses...</div>
                </div>
            </td>
        </tr>
    `);
    $row.after($newRow);

    const $responseDiv = $newRow.find('.response-content');

    // Fetch response data
    $.ajax({
        url: admin_url + 'client/get_testimonial_responses',
        method: 'POST',
        data: { 
            id: id, 
            <?= json_encode($this->security->get_csrf_token_name()) ?>: <?= json_encode($this->security->get_csrf_hash()) ?>
        },
        success: function (res) {
            let html = '<table class="table table-striped table-bordered">';
            html += '<thead><tr>' +
                        '<th>Name & Email</th>' +
                        '<th>Rating</th>' +
                        '<th>Text Response</th>' +
                        '<th>Images</th>' +
                        '<th>Show in Wall</th>' +
                        '<th>Video Response</th>' +
                        '<th>Created At</th>' +
                    '</tr></thead><tbody>';

            if (!Array.isArray(res) || res.length === 0) {
                html += '<tr><td colspan="7" class="text-center text-muted">No responses found.</td></tr>';
            } else {
                res.forEach(item => {
    html += `<tr>
        <td>${item.name || '-'}<br><small>${item.email || '-'}</small></td>
        <td>${renderStars(item.rating)}</td>
        <td>${item.text_response || '-'}</td>
        <td>${
            item.images
                ? flextestimonial_perfect_unserialize_js(item.images).map(image => {
                    return `<a href="${flextestimonial_media_url(image)}" target="_blank">
                        <img src="${flextestimonial_media_url(image)}" alt="Image" style="max-width:50px; max-height:50px;" />
                    </a>`;
                  }).join('')
                : '—'
        }</td>
        <td>${item.featured == 1 ? 'Yes' : 'No'}</td>
        <td>${
            item.video_url 
                ? `<a href="${site_url}uploads/flextestimonial/${item.video_url}" target="_blank">Video</a>`
                : '—'
        }</td>
        <td>${item.created_at && item.created_at !== '0000-00-00 00:00:00' ? item.created_at : '-'}</td>
    </tr>`;
});

            }

            html += '</tbody></table>';
            $responseDiv.html(html);
        },
        error: function () {
            $responseDiv.html('<div class="text-danger p-2">Failed to fetch data. Please try again.</div>');
        }
    });
});

</script>
