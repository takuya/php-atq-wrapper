<?php


namespace SystemUtil\AtJobCommand;


use SystemUtil\Process;

class AtCommand {
  
  public static $command_path='at';
  public static function exec(...$args): array {
    $args = static::genArgs($args);
    $proc = new Process( $args );
    $proc->run();
    return [$proc->getExitCode(), $proc->getOutput(), $proc->getErrorOutput()];
  }
  protected static function genArgs($args): array {
    $cmd = preg_split('/\s+/', static::$command_path);
    $args = array_merge( [], $cmd, ...[$args] );
    $args = array_filter($args,'trim');
    return $args;
  }
  public static function getJob( $id,$q=null): array {
    $args = array_merge([],['-c',$id], $q?['-q',$q]:[]);
    $ret =  static::exec(...$args);
    return $ret;
  }
  public static function removeJob($id, $q=null): array {
    $args = array_merge([],['-d',$id], $q?['-q',$q]:[]);
    $ret =  static::exec(...$args);
    return $ret;
  }
  public static function listJobs($q=null): array {
    $args = array_merge([],['-l'], $q?['-q',$q]:[]);
    $ret =  static::exec(...$args);
    return $ret;
  }
  
  /**
   * Add job to atq. with script body executed.
   *  Time format is [ HH:mm MM/DD/YY,HH:mm DD.MM.YY,HH:mm MMDDY]
   * ex. [15:47 04/30/12, 15:47 043012, 15:47 30.04.12]
   * or time diff from now [ +1day , +1week , next Thu]
   * TODO:: pass environment variables.環境変数の受け渡しをどうするの。
   * @param $at_time    string The time at job executed.
   * @param $sh_body string /bin/sh script body.
   * @param mixed ...$args options such as -q $x. ( $x is [a-z] single letter.) / -m ( notify mail ) / -M ( no mail notify ).
   */
  public static function addJobWithScriptBody( $at_time, $sh_body , ...$args ): array {
    $args = static::genArgs( array_merge([],[$at_time],$args));
    $proc = new Process( $args );
    $proc->setInput($sh_body);
    $proc->run();
    //
    return [$proc->getExitCode(), $proc->getOutput(), $proc->getErrorOutput()];
  }
  
  public static function addJobWithFilename ( $at_time, $f_path , ...$args ): array {
    $args = static::genArgs( array_merge([],[$at_time],['-f',$f_path],$args));
    $proc = new Process( $args );
    $proc->run();
    //
    return [$proc->getExitCode(), $proc->getOutput(), $proc->getErrorOutput()];
  }
}