<?php


namespace Tests\Units\AtJob;


use Tests\TestCase;
use SystemUtil\AtJobs;
use SystemUtil\AtJobCommand\AtqCommand;
use SystemUtil\AtQueueJob;
use SystemUtil\AtQueueList;

class AtJobTest extends TestCase {
  
  public function test_at_job_sort_delete(){
    $at_job = new AtJobs( 'ssh s1 lxc exec ubuntu1604 -- at' );
  
    $ids = [];
    foreach (range(1,3) as $i){
      $ids[]=$at_job->add( new \DateTime( "+${i}week" ), 'echo Hello','X' );
    }
    foreach ( $at_job->queue("X")->sort('id') as $e){
      $e->remove();
    }
    $ret = $at_job->exists($ids[0])
    && $at_job->exists($ids[1])
    && $at_job->exists($ids[2]);
    $this->assertFalse($ret);
    //
    $ids = [];
    foreach (range(1,3) as $i){
      $ids[]=$at_job->add( new \DateTime( "+${i}week" ), 'echo Hello','X' );
    }
    foreach ( $at_job->queue("X")->sort('start') as $e){
      $e->remove();
    }
    $ret = $at_job->exists($ids[0])
      && $at_job->exists($ids[1])
      && $at_job->exists($ids[2]);
    $this->assertFalse($ret);

    //
    $ids = [];
    foreach (range(1,3) as $i){
      $ids[]=$at_job->add( new \DateTime( "+${i}week" ), 'echo Hello','X' );
    }
    $at_job->remove($ids[0]);
    $at_job->remove($ids[1]);
    $at_job->remove($ids[2]);
    $ret = $at_job->exists($ids[0])
      && $at_job->exists($ids[1])
      && $at_job->exists($ids[2]);
    $this->assertFalse($ret);
    //
    $ids = [];
    foreach (range(1,3) as $i){
      $ids[]=$at_job->add( new \DateTime( "+".($i*10)."days" ), 'echo Hello','X' );
    }
    ($at_job->queue("X"))[0]->remove();
    ($at_job->queue("X"))[0]->remove();
    ($at_job->queue("X"))[0]->remove();
    $ret = $at_job->exists($ids[0])
      && $at_job->exists($ids[1])
      && $at_job->exists($ids[2]);
    $this->assertFalse($ret);
  }
  public function test_at_job_class_crud () {
    $at_job = new AtJobs( 'ssh s1 lxc exec ubuntu1604 -- at' );
    //
    $id = $at_job->add( new \DateTime( '+1week' ), 'echo Hello', 'C' );
    $this->assertIsNumeric( $id );
    
    $ids = $at_job->list_id("C");
    $this->assertContains( $id, $ids );
    foreach ( $ids as $id ) {
      $this->assertIsNumeric( $id );
    }
    $info = $at_job->get_body( $id );
    $this->assertNotEmpty( $info );
    $ret = $at_job->remove( $id );
    $this->assertTrue( $ret );
  }
  public function test_orderby_job_list(){
    $at_job = new AtJobs( 'ssh s1 lxc exec ubuntu1604 -- at' );
    $ids =[];
    $ids[] = $at_job->add( new \DateTime( '+30days' ), 'echo Hello 2','B' );
    $ids[] = $at_job->add( new \DateTime( '+10days' ), 'echo Hello 2','B' );
    $ids[] = $at_job->add( new \DateTime( '+20days' ), 'echo Hello 2','B' );
    $job_queue = $at_job->queue('B');
    //
    $ret = $job_queue->sort('id');
    $this->assertGreaterThan($ret[0]->id, $ret[1]->id);
    $ret = $job_queue->sort('id','desc');
    $this->assertGreaterThan($ret[1]->id, $ret[0]->id);
    //
    $ret = $job_queue->sort('start');
    $this->assertGreaterThan($ret[0]->start, $ret[1]->start);
    $ret = $job_queue->sort('start','desc');
    $this->assertGreaterThan($ret[1]->start, $ret[0]->start);
    //
    foreach ($ids as $id){
      $at_job->remove($id);
      $ret = $at_job->exists($id);
      $this->assertFalse($ret);
    }
  }
  
  public function test_access_as_iterator () {
    
    $at_job = new AtJobs( 'ssh s1 lxc exec ubuntu1604 -- at' );
    $ids[] = $at_job->add( new \DateTime( '+3days' ), 'echo Hello 2','Y' );
    $ids[] = $at_job->add( new \DateTime( '+10days' ), 'echo Hello 2','Y' );
    $ids[] = $at_job->add( new \DateTime( '+10days' ), 'echo Hello 2','Y' );
    $job_queue = $at_job->queue('Y');
    $this->assertEquals( AtQueueList::class, get_class( $job_queue ) );
    foreach ( $job_queue as $idx => $e ) {
      $this->assertEquals( AtQueueJob::class, get_class( $e ) );
    }
    $list = $at_job->queue('Y');
    foreach ( $list as $key =>$e){
      $id= $e->id;
      $e->remove();
      $this->assertFalse($at_job->exists($id));
    }
    $this->assertEquals(0,$at_job->queue('Y')->count());
    
  }
    public function test_add_job_several_timespec(){
    $at_job = new AtJobs( 'ssh s1 lxc exec ubuntu1604 -- at' );
    $q = 'Z';
    
    $timespecs = [
      'next monday',
      new \DateTime( '+30days' ),
      (new \DateTime('next monday'))->getTimestamp(),
      (new \DateTime('next monday 10:00'))->format('Y-m-d H:i:s'),
    ];
    foreach ( $timespecs as $timespec ) {
      $ids[]=$at_job->add( $timespec , 'echo Hello 2',$q );
    }
    [$a, $b] = [$at_job->list_id($q),$ids];
    $this->assertEquals(sort($b),sort($a));
    foreach ( $at_job->queue($q)->sort('id') as $i=> $job){
      $this->assertEquals($ids[$i],$job->id);
    }
    foreach ( $at_job->queue($q)->sort('id') as $i=> $job){
      $t = $timespecs[$i];
      $t = is_string($t)? new \DateTime($t):$t;
      $t = is_int($t) ? (new \DateTime())->setTimestamp($t):$t;
      $this->assertEquals($t->format('Y-m-d H:i'),$job->start->format('Y-m-d H:i'));
    }

    foreach ( $at_job->queue($q)->sort('id') as $i=> $job){
      $job->remove();
      $ret = $at_job->exists($ids[$i]);
      $this->assertFalse($ret);
    }
  }
}