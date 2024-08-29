<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Utility;

//use PHP\Imagick;
/**
 * Description of PDFDocument
 *
 * @author NMammedov
 */
class PDFDocument {

    //put your code here
    public function getPreviewImage($file, $page) {
        $imagick = new \Imagick();
        $pathinfo = $xmlFile = pathinfo($file);
        $directory = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];
        $result = [
            'error' => 0,
            'message' => 'ok'
        ];
        try {
            if (!file_exists($file)) {
                $result['error'] = 16001;
                $result['message'] = "File does not exist: " . $file;
            } else {
                $imagick->readImage($file);
                //$imagick->writeImages($directory . '/' . $filename . '_cover.jpg', false);
            }
        } catch (\Exception $ex) {
            $result['error'] = $ex->getCode();
            $result['message'] = $ex->getMessage() . " " . $ex->getFile() . " " . $ex->getLine() . " File:" . $file . '[' . $page . ']' . ' Output:' . $directory . '/' . $filename . '_cover.jpg';
        }
        return $result;
    }

    function create_preview($file, $page, $image_format="jpeg", $image_antialiasing="4", $image_resolution="300") {
        $result = [
            'error' => 0,
            'message' => 'ok'
        ];

        $pathinfo = $xmlFile = pathinfo($file);
        $directory = $pathinfo['dirname'];
        $filename = $pathinfo['filename'];

        $output_format = $image_format;
        $antialiasing = $image_antialiasing;
        $preview_page = $page;
        $resolution = $image_resolution;
        $output_file = $directory . "/" . $filename . "_".$page.".jpg";

        $exec_command = "gswin64 -dSAFER -dBATCH -dNOPAUSE -sDEVICE=" . $output_format . " ";
        $exec_command .= "-dTextAlphaBits=" . $antialiasing . " -dGraphicsAlphaBits=" . $antialiasing . " ";
        $exec_command .= "-dFirstPage=" . $preview_page . " -dLastPage=" . $preview_page . " ";
        $exec_command .= "-r" . $resolution . " ";
        $exec_command .= "-sOutputFile=" . $output_file . " \"" . $file . "\"";

        //echo "Executing command...\n";
        //exec($exec_command, $command_output, $return_val);
        $output = $exec_command."<br>";
        $output .= shell_exec($exec_command);
//        $output = '';
//        foreach ($command_output as $line) {
//            $output .= $line . "\n";
//        }

//        if (!$return_val) {
//            $result['message'] = "Preview created successfully!!\n";
//        } else {
//            $result['error'] = $return_val;
            $result['message'] = $output;
//        }

        return $result;
    }

}
