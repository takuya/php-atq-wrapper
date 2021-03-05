<?php


function dd ( ...$args ) {
  //var_dump(debug_backtrace());
  dump( ...$args );
  exit;
}

;

function dump ( ...$args ) {
  foreach ( func_get_args() as $e ) {
    var_dump( $e );
  }
}

;

function ascii_letters(){
  $ascii_letters = array_map(function($e){return chr($e);},array_merge(range(65,90),range(97,122)));
  return $ascii_letters;
}