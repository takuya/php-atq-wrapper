<?php


namespace SystemUtil\AtJobCommand;


use SystemUtil\Process;

class AtqCommand {
  public static $command_path='atq';
  public static function exec(...$args){
    $cmd = preg_split('/\s+/', static::$command_path);
    $args = array_merge( [], $cmd, ...[$args] );
    $args = array_filter($args,'trim');
    $proc = new Process( $args );
    $proc->run();
    return [$proc->getExitCode(), $proc->getOutput(), $proc->getErrorOutput()];
  }
  public static function listJobs($q=null){
    return static::exec( ...(!empty($q)?['-q',$q]:[]) );
  }

}