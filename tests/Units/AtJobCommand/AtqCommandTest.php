<?php

namespace Tests\Units\AtJobCommand;
use Tests\TestCase;
use SystemUtil\AtJobCommand\AtqCommand;

class AtqCommandTest extends TestCase {
  
  public function test_atq_list_command(){
    AtqCommand::$command_path='ssh s1 lxc exec ubuntu1604 -- atq';
    $ret = AtqCommand::listJobs();
    $this->assertEquals(0, $ret[0]);

  }
}