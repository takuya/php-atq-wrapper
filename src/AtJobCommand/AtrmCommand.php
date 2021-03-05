<?php


namespace SystemUtil\AtJobCommand;


use SystemUtil\Process;

class AtrmCommand {
  public static $command_path='atrm';
  public static function exec(...$args){
    $cmd = preg_split('/\s+/', static::$command_path);
    $args = array_merge( [], $cmd, ...[$args] );
    $args = array_filter($args,'trim');
    $proc = new Process( $args );
    $proc->run();
    return [$proc->getExitCode(), $proc->getOutput(), $proc->getErrorOutput()];
  }
  public static function removeJob($id, $q=null){
    $args = array_merge([],[$id], $q?['-q',$q]:[]);
    $ret =  static::exec(...$args);
    return $ret;
  }

}