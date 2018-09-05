<?php


function addLog( $content){
    // using the FILE_APPEND flag to append the content to the end of the file
    // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
    
    $content.="\n";
    file_put_contents('jomr.log', $content, FILE_APPEND | LOCK_EX);
}