<?php


namespace SystemUtil;

/**
 * User: takuya
 * Date: 20170728
 * Time: 16:29
 */
class AtQueueList implements \Countable, \Iterator,\ArrayAccess {
  
  protected $idx = 0;
  protected $list;
  /** @var AtJobs */
  protected $_aggregator;
  protected $order;
  protected $order_key;
  
  protected function __construct ( $arr, $at_jobs ) {
    $this->_aggregator = $at_jobs;
    $this->idx = 0;
    foreach ( $arr as $i => $item ) {
      $arr[$i] = AtQueueJob::factory( $item, $this );
    }
    $this->list = $arr;
    $this->sort();
  }
  public static function factory($arr,$at_jobs): AtQueueList {
    return  new self($arr,$at_jobs);
  }
  
  public function getParent (): AtJobs {
    return $this->_aggregator;
  }
  protected function findIndex($id){
    foreach ( $this->list as $i=> $item ) {
      if ( $item && $item->id == $id ){
        return $i;
      }
    }
    return null;
  }
  
  /**
   * @return  AtQueueJob[]
   */
  public function sort( $key='id', $order='asc'){
    $this->order_key=$key;
    $this->order=$order;
    usort($this->list, function( $a,$b ) use ($key, $order){
      return (  (($order != 'desc') ? ($a->$key < $b->$key):($a->$key > $b->$key) )? -1 : 1) ;
    });
    return $this->list;
  }
  ////Iterator
  public function current ():AtQueueJob {return $this->list[$this->idx];}
  public function key () { return $this->idx; }
  public function next () { return $this->idx++; }
  public function rewind () { return $this->idx=0; }
  public function valid () { return $this->idx < $this->count(); }
  
  // Coountable
  public function count () { return sizeof( $this->list ); }
  // ArrayAccess
  public function offsetExists ( $offset ) {return array_key_exists($offset,$this->list);}
  public function offsetGet ( $offset ) {return $this->list[$offset];}
  public function offsetSet ( $offset, $value ) {
    if ( get_class($value)!=AtQueueJob::class){
      throw new \InvalidArgumentException();
    }
    $this->list[$offset] = $value;
  }
  public function offsetUnset ( $offset ) {
    $this->removeAt($offset);
  }
  
  // sync remove with list
  protected function removeAt( $index): bool {
    $item = $this->list[$index];
    $ret=$this->_aggregator->remove($item->id);
    unset($this->list[$index]);
    // fix foreach.
    ($index == $this->idx )&& $this->idx--;
    $this->list = array_values($this->list);
    $this->sort($this->order_key,$this->order);
    return $ret;
  }
  public function remove($id): bool {
    $idx = $this->findIndex($id);
    return $this->removeAt($idx);
  }
  
  
  
}