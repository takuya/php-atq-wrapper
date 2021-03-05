<?php


namespace SystemUtil;

class AtQueueJob {
  
  public $id;
  public $q;
  public $start;
  public $user;
  /** @var AtQueueList */
  protected $_parent;
  
  public static function factory($data, $parent):AtQueueJob{
    return new self($data,$parent);
  }
  protected function __construct ($data, $parent) {
    $this->_parent = $parent;
    foreach (['id','start','q','user'] as $key ){
      $this->$key = $data[$key];
    }
    $this->start = (new \DateTime($this->start));
    $this->id = intval($this->id);
  }
  public function body(): string {
    return $this->_parent->getParent()->get_body($this->id);
  }
  public function remove (): bool {
    return  $this->_parent->remove($this->id);
  }
  
}
