<?php


namespace AtJobCommand;


use Tests\TestCase;
use SystemUtil\AtJobCommand\AtCommand;
use SystemUtil\AtJobCommand\AtqCommand;

class AtCommandTest  extends TestCase {
  
  public function test_atcmd_crud_at_job(){
    AtCommand::$command_path= 'ssh s1 lxc exec ubuntu1604 -- at';
    AtqCommand::$command_path= 'ssh s1 lxc exec ubuntu1604 -- atq';
    $date = (new \DateTime())->modify('+1week')->format('H:i m/d/y');
    
    $ascii_letters = ascii_letters();
    $qname = $ascii_letters[rand(0,sizeof($ascii_letters)-1)];
    //
    $ret = AtCommand::addJobWithScriptBody($date, 'echo Hello '.__FUNCTION__, '-q', $qname);
    $patten = '/.*job\s(\d+).+/s';
    $this->assertGreaterThan(0,preg_match($patten, $ret[2]));
    $id = preg_filter($patten, '$1', $ret[2]);
    $this->assertIsNumeric($id);
    //
    $ret = AtCommand::listJobs();
    $count_at_cmd = preg_match_all('/^\d+.+$/m', $ret[1]);
    $this->assertStringContainsString($id,$ret[1]);
    //
    AtqCommand::$command_path='ssh s1 lxc exec ubuntu1604 -- atq';
    $ret = AtqCommand::listJobs();
    $count_atq_cmd = preg_match_all('/^\d+.+$/m', $ret[1]);
    $this->assertEquals($count_atq_cmd,$count_at_cmd);
    ///
    $ret = AtCommand::getJob($id);
    $this->assertEquals(0, $ret[0]);
    $this->assertEmpty($ret[2]);
    $this->assertNotEmpty($ret[1]);
    //
    $ret = AtCommand::removeJob($id);
    $this->assertEquals(0, $ret[0]);
  }
  public function test_atcmd_crud_execute_file_at_job(){
    AtCommand::$command_path= 'ssh s1 lxc exec ubuntu1604 -- at';
    AtqCommand::$command_path= 'ssh s1 lxc exec ubuntu1604 -- atq';
    $date = (new \DateTime())->modify('+1week')->format('H:i m/d/y');
    
    $ascii_letters = ascii_letters();
    $qname = $ascii_letters[rand(0,sizeof($ascii_letters))];
    //
    $ret = AtCommand::addJobWithFilename($date, '/no/such/a/file', '-q', $qname);
    $this->assertNotEquals(0,$ret[0]);
    $this->assertStringContainsString('Cannot open input file',$ret[2]);
    
    //
    $ret = AtCommand::addJobWithFilename($date, '/etc/profile', '-q', $qname);
    $patten = '/.*job\s(\d+).+/s';
    $this->assertGreaterThan(0,preg_match($patten, $ret[2]));
    $id = preg_filter($patten, '$1', $ret[2]);
    $this->assertIsNumeric($id);
    //
    $ret = AtCommand::listJobs();
    $count_at_cmd = preg_match_all('/^\d+.+$/m', $ret[1]);
    $this->assertStringContainsString($id,$ret[1]);
    //
    AtqCommand::$command_path='ssh s1 lxc exec ubuntu1604 -- atq';
    $ret = AtqCommand::listJobs();
    $count_atq_cmd = preg_match_all('/^\d+.+$/m', $ret[1]);
    $this->assertEquals($count_atq_cmd,$count_at_cmd);
    ///
    $ret = AtCommand::getJob($id);
    $this->assertEquals(0, $ret[0]);
    $this->assertEmpty($ret[2]);
    $this->assertNotEmpty($ret[1]);
    //
    $ret = AtCommand::removeJob($id);
    $this->assertEquals(0, $ret[0]);
  }
}