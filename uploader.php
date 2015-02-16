<?php

define('WP_USE_THEMES', false);
require('../../../../../../wp-blog-header.php');
if (!function_exists ('has_action')) {
    header('Status: 403 Forbidden');
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
    die('-1');
}
header($_SERVER['SERVER_PROTOCOL'] . ' 200 Ok');
header('application/json; charset=' . get_option('blog_charset'));

if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );

class Boots_Form_Uploader
{
    private $Upload;
    private $uploadpath;
    private $File;
    private $filename;
    private $tempname;

    public function __construct()
    {
        $this->Upload = wp_upload_dir();
        $this->uploadpath = $this->Upload['path'];

        if(!$_FILES) $this->fail();
        if(!isset($_FILES["file"])) $this->fail();
        if(isset($_FILES["file"]["error"]) && $_FILES["file"]["error"])
            $this->fail();

        $this->File = $_FILES["file"];
        $this->filename = $this->File['name'];
        $this->tempname = $this->File['tmp_name'];

        $this->upload();
    }

    private function fail()
    {
        die(json_encode(array('OK' => 0)));
    }

    private function success($data)
    {
        die(json_encode($data));
    }

    private function upload()
    {
        //move_uploaded_file($this->tempname, $this->uploadpath . '/' . $this->filename);
        if($uploaded = wp_handle_upload($this->File, array(
            'test_form' => false
        ))) $this->success($uploaded);
        else $this->fail();
    }
}
new Boots_Form_Uploader();