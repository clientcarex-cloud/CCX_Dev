<?php

function handle_doctor_signature_upload($doctor_id)
{
    $path          = get_upload_path_by_type_client('doctor') . $doctor_id . '/';
    $CI            = &get_instance();
    $totalUploaded = 0;

    if (isset($_FILES['signature']['name']) && $_FILES['signature']['name'] != '') {
        _file_attachments_index_fix('signature');

        $tmpFilePath = $_FILES['signature']['tmp_name'];
        if (!empty($tmpFilePath)) {
            if (_healtho_upload_error_client($_FILES['signature']['error']) || !_upload_extension_allowed_client($_FILES['signature']['name'])) {
                return false;
            }

            _maybe_create_upload_path_client($path);

            $filename    = unique_filename_client($path, $_FILES['signature']['name']);
            $newFilePath = $path . $filename;

            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                if (is_image_client($newFilePath)) {
                    create_img_thumb_client($newFilePath, $filename);
                }

                // Save the file name to tbldoctor_new_fields
                $CI->db->where('doctor_id', $doctor_id);
                $CI->db->update(db_prefix() . 'doctor_new_fields', ['signature' => $filename]);

                $totalUploaded++;
            }
        }
    }

    return 'uploads/doctor/' . $doctor_id . '/' . $filename;
}

function get_upload_path_by_type_client($type)
{
    // Define custom directories based on type
    switch ($type) {
        case 'doctor':
            return FCPATH . 'uploads/doctor_signatures/';
        default:
            return FCPATH . 'uploads/';
    }
}

function _maybe_create_upload_path_client($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
        fopen($path . 'index.html', 'w'); // Prevent directory listing
    }
}

function unique_filename_client($path, $filename)
{
    $filename = strtolower(preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $filename));
    $pathinfo = pathinfo($filename);
    $base     = $pathinfo['filename'];
    $ext      = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';
    $i        = 1;

    while (file_exists($path . $filename)) {
        $filename = $base . '_' . $i . $ext;
        $i++;
    }

    return $filename;
}

function _upload_extension_allowed_client($filename)
{
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed_extensions);
}

function _healtho_upload_error_client($error_code)
{
    return $error_code !== UPLOAD_ERR_OK;
}

function is_image_client($path)
{
    $type = exif_imagetype($path);
    return in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WEBP]);
}

function create_img_thumb_client($path, $filename)
{
    $CI = &get_instance();
    $CI->load->library('image_lib');

    $config['image_library']  = 'gd2';
    $config['source_image']   = $path;
    $config['create_thumb']   = TRUE;
    $config['maintain_ratio'] = TRUE;
    $config['width']          = 150;
    $config['height']         = 150;
    $CI->image_lib->initialize($config);
    $CI->image_lib->resize();
    $CI->image_lib->clear();
}
