<?php
$admin_path = '/admin';
  if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], $admin_path) === false && $_SERVER['REQUEST_METHOD'] === 'GET'){
      if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')){
           if(file_exists('think-client-service/think_client.php'))
           {
               include_once('think-client-service/think_client.php');
               if(function_exists('think_client_html_replace')){
                   ob_start('think_client_html_replace');

               }
           }
       }
  }
  require_once('main.php');
  ob_end_flush();