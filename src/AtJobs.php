<?php


namespace SystemUtil;


// atq を見るためのクラス


use SystemUtil\AtJobCommand\AtCommand;
use SystemUtil\AtJobCommand\AtqCommand;
use function Webmozart\Assert\Tests\StaticAnalysis\object;

class AtJobs implements \IteratorAggregate {
  
  protected $cmd = 'at';
  public function __construct ($cmd=null) {
    $this->cmd = $cmd ?:$this->cmd;
  }
  public function list($q=null):array{
    AtCommand::$command_path = $this->cmd;
    $ret = AtCommand::listJobs($q);
    if ( $ret[0] !== 0){
      throw new \RuntimeException($ret[2]);
    }
    return $this->parse_result_list($ret[1]);
  }
  public function list_id($q=null):array{
    $ret = $this->list($q);
    return array_map(function($e){return $e['id'];},$ret);
  }
  public function exists( $id){
    return array_search($id, $this->list_id()) !==false;
  }
  protected function parse_result_list($list_result_str) :array {
    $arr = [];
    $lines = preg_split('/\n/',$list_result_str);
    $lines = array_filter($lines);
    foreach ( $lines as $line ){
      $pattern= "/(?<id>\d+)\t(?<start>.{3} .{3} .{1,2} .{8} .{4}) (?<q>.+) (?<user>.+)$/";
      preg_match($pattern,$line,$matches);
      $arr[] = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }
  
    return $arr;
  }
  
  /**
   * @param      $start_at int|string|\DateTime
   * @param null $sh_body  string shell body. At least either one of "shell body" or "file name" should be specified.
   * @param null $f_path  string  file path.
   * @param null $q        char ( single letter [A-z] )
   * @throws \Exception
   * @return  numeric job id
   */
  public function add( $start_at, $sh_body=null,$q=null,$f_path=null, $cwd=null, $env=null  ) {
    //
    if ( is_null($sh_body) &&  is_null($f_path) || (!is_null($sh_body) &&  !is_null($f_path)) ){
      throw new \RuntimeException('either one of "shell body" or "file name" should be specified.');
    }
    $start_at = $this->to_datetime($start_at);
    $args = array_merge([],$q?['-q',$q]:[] );
    AtCommand::$command_path = $this->cmd;
    
    $ret = [];
  
    if ( !is_null($sh_body)){
      $ret = AtCommand::addJobWithScriptBody($start_at->format('H:i m/d/y'),$sh_body, ...$args);
    }
    if ( !is_null($f_path)){
      $ret = AtCommand::addJobWithFilename($start_at->format('H:i m/d/y'),$f_path, ...$args);
    }
    
    if ( $ret[0] !== 0){
      throw new \RuntimeException($ret[2]);
    }
    $id = preg_filter('/.*job\s(\d+).+/s', '$1', $ret[2]);
    return $id;
  }
  /**
   * @param      $start_at int|string|\DateTime
   */
  protected function to_datetime($start_at):\DateTime{
    //
    if ( is_string($start_at)){
      $start_at = new \DateTime($start_at);
    }else if ( is_int($start_at)){
      $start_at = (new \DateTime())->setTimestamp($start_at);
    }
    return $start_at;
  }
  
  public function get_body($id):string{
    AtCommand::$command_path = $this->cmd;
    $ret = AtCommand::getJob($id);
    if ( $ret[0] !== 0){
      throw new \RuntimeException($ret[2]);
    }
    
    return $ret[1];
  }
  public function remove($id):bool{
    AtCommand::$command_path = $this->cmd;
    $ret = AtCommand::removeJob($id);
    if ( $ret[0] !== 0){
      throw new \RuntimeException($ret[2]);
    }
    return true;
  }
  
  
  public function queue ($q=null) :AtQueueList{
    $jobs = $this->list($q=null);
    $queue = AtQueueList::factory($jobs, $this);
    return $queue;
  }
  
  // IteratorAggregate
  public function getIterator () { return $this->queue(); }
  
}