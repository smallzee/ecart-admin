<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;

require_once 'includes/crud.php';
$db = new Database();
$db->connect();

if (ALLOW_MODIFICATION == 0 && !defined(ALLOW_MODIFICATION)) {
    echo '<label class="alert alert-danger">This operation is not allowed in demo panel!.</label>';
    return false;
}
$count = count($_FILES['documents']['name']);
for ($i = 0; $i < $count; $i++) {
    if (!empty($_FILES['documents']['name'][$i])) {
        $image_name = $db->escapeString($fn->xss_clean($_FILES['documents']['name'][$i]));
        $image_type =  $db->escapeString($fn->xss_clean($_FILES['documents']['type'][$i]));
        $tmp_name =  $db->escapeString($fn->xss_clean($_FILES['documents']['tmp_name'][$i]));
        $image_error =  $db->escapeString($fn->xss_clean($_FILES['documents']['error'][$i]));
        $size =  $db->escapeString($fn->xss_clean($_FILES['documents']['size'][$i]));

        $target_path = 'upload/media/';
        $error = array();
        // $extension = end(explode(".", $_FILES['documents']['name'][$i]));
        $arr = explode(".", $image_name);
        $extension = strtolower(array_pop($arr));

        $allowedExts = array(
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'eps'],
            'video' => ['mp4', '3gp', 'avchd', 'avi', 'flv', 'mkv', 'mov', 'webm', 'wmv', 'mpg', 'mpeg', 'ogg'],
            'document' => ['doc', 'docx', 'txt', 'pdf', 'ppt', 'pptx'],
            'spreadsheet' => ['xls', 'xsls'],
            'archive' => ['zip', '7z', 'bz2', 'gz', 'gzip', 'rar', 'tar'],
        );
        // function find_type($extension, $allowedExts)
        // {
        //     foreach ($allowedExts as $main_type => $extenstions) {
        //         foreach ($extenstions as $k => $v) {
        //             if ($v === strtolower($extension)) {
        //                 return $main_type;
        //             }
        //         }
        //     }
        //     return false;
        // }
        // $type = find_type($extension, $allowedExts);
        error_reporting(E_ERROR | E_PARSE);

        // upload new image
        if (!is_dir($target_path)) {
            mkdir($target_path, 0777, true);
        }
        $image = time() . rand('1000', '9999') . "." . $extension;
        if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], 'upload/media/' . $image)) {
            $sub_directory = 'upload/media/';
            // insert new data to menu table
            $sql = "INSERT INTO `media`(`name`,`extension`,`type`,`sub_directory`,`size`) VALUES ('" . $image . "','" . $extension . "','" . $image_type . "','" . $sub_directory . "','" . $size . "')";
            $db->sql($sql);
            $res = $db->getResult();

            $sql = "SELECT id FROM `media` ORDER BY id DESC";
            $db->sql($sql);
            $res = $db->getResult();

            $response['error'] = false;
            $response['message'] = "<p class='alert alert-success'>Image Uploaded Successfully</p>";
        }
    } else {
        $response['error'] = true;
        $response['message'] = "<p class='alert alert-danger'>Image could not be Uploaded!Try Again</p>";
    }
}
echo json_encode($response);
