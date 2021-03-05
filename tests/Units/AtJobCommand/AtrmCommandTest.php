<?php


namespace AtJobCommand;


use Tests\TestCase;
use SystemUtil\AtJobCommand\AtCommand;
use SystemUtil\AtJobCommand\AtqCommand;
use SystemUtil\AtJobCommand\AtrmCommand;

class AtrmCommandTest  extends TestCase {
  
  public function test_atrm_remove_at_job(){
    AtCommand::$command_path= 'ssh s1 lxc exec ubuntu1604 -- at';
    AtrmCommand::$command_path= 'ssh s1 lxc exec ubuntu1604 -- atrm';
    $date = (new \DateTime())->modify('+1week')->format('H:i m/d/y');
    
    $ascii_letters = ascii_letters();
    $qname = $ascii_letters[rand(0,sizeof($ascii_letters)-1)];
    //
    $ret = AtCommand::addJobWithScriptBody($date, 'echo Hello', '-q', $qname);
    $patten = '/.*job\s(\d+).+/s';
    $this->assertGreaterThan(0,preg_match($patten, $ret[2]));
    $id = preg_filter($patten, '$1', $ret[2]);
    $this->assertIsNumeric($id);
    //
    $ret = AtrmCommand::removeJob($id);
    $this->assertEquals(0, $ret[0]);
    //
    $ret = AtCommand::getJob($id);
    $this->assertNotEquals(0, $ret[0]);
  }
}