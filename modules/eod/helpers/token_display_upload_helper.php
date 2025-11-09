<?php

defined('BASEPATH') or exit('No direct script access allowed');

function token_display_upload_logo($display_id)
{
    $path = token_display_get_upload_path('logo') . $display_id . '/';
    $CI   = &get_instance();

    if (isset($_FILES['display_logo']['name']) && $_FILES['display_logo']['name'] != '') {
        _file_attachments_index_fix('display_logo');

        $tmpFilePath = $_FILES['display_logo']['tmp_name'];
        if (!empty($tmpFilePath)) {
            if (token_display_has_upload_error($_FILES['display_logo']['error']) || !token_display_extension_allowed($_FILES['display_logo']['name'])) {
                return false;
            }

            token_display_ensure_upload_path($path);

            $filename     = token_display_unique_filename($path, $_FILES['display_logo']['name']);
            $newFilePath  = $path . $filename;

            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                if (token_display_is_image($newFilePath)) {
                    token_display_create_thumb($newFilePath, $filename);
                }
                return 'uploads/display_logo/' . $display_id . '/' . $filename;
            }
        }
    }

    return null;
}

function token_display_upload_slider_images($display_id)
{
    $path = token_display_get_upload_path('slider') . $display_id . '/';
    $CI   = &get_instance();

    $uploaded = [];

    if (!empty($_FILES['slider_images']['name'][0])) {
        token_display_ensure_upload_path($path);
        $files = $_FILES;

        $count = count($_FILES['slider_images']['name']);
        for ($i = 0; $i < $count; $i++) {
            $_FILES['slider']['name']     = $files['slider_images']['name'][$i];
            $_FILES['slider']['type']     = $files['slider_images']['type'][$i];
            $_FILES['slider']['tmp_name'] = $files['slider_images']['tmp_name'][$i];
            $_FILES['slider']['error']    = $files['slider_images']['error'][$i];
            $_FILES['slider']['size']     = $files['slider_images']['size'][$i];

            $tmpFilePath = $_FILES['slider']['tmp_name'];
            if (!empty($tmpFilePath)) {
                if (token_display_has_upload_error($_FILES['slider']['error']) || !token_display_extension_allowed($_FILES['slider']['name'])) {
                    continue;
                }

                $filename     = token_display_unique_filename($path, $_FILES['slider']['name']);
                $newFilePath  = $path . $filename;

                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                    $CI->db->insert('tbldisplay_images', [
                        'display_id' => $display_id,
                        'image_path' => 'uploads/display_slider/' . $display_id . '/' . $filename
                    ]);
                    $uploaded[] = $filename;
                }
            }
        }
    }

    return $uploaded;
}

function token_display_get_upload_path($type)
{
    switch ($type) {
        case 'logo':
            return FCPATH . 'uploads/display_logo/';
        case 'slider':
            return FCPATH . 'uploads/display_slider/';
        default:
            return FCPATH . 'uploads/';
    }
}

function token_display_ensure_upload_path($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0755, true);
        file_put_contents($path . 'index.html', ''); // Prevent directory listing
    }
}

function token_display_unique_filename($path, $filename)
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

function token_display_extension_allowed($filename)
{
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed_extensions);
}

function token_display_has_upload_error($error_code)
{
    return $error_code !== UPLOAD_ERR_OK;
}

function token_display_is_image($path)
{
    $type = @exif_imagetype($path);
    return in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP, IMAGETYPE_WEBP]);
}

function token_display_create_thumb($path, $filename)
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
