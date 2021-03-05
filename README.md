# atq/at/atrm command php wrapper

'atq/at' scheduled job timer command line wrapper.

## Install 

```shell
composer require takuya/php-atq-wrapper
```

## Samples

### add/show/remove atq job

```php
<?php
    use SystemUtil\AtJobs;
    $at_job = new AtJobs( 'ssh server docker exec ubuntu1604 -- at' );
    $id = $at_job->add( '+30days', 'echo Hello 2','s' );
    $at_job->exists($id);// => true
    $at_job->get_body( $id ); //=> /bin/sh body will be executed.
    $at_job->remove($id); //=> cancel job.
```

### list current queued jobs.

```php
<?php
    use SystemUtil\AtJobs;
    $at_job = new AtJobs( 'ssh server docker exec ubuntu1604 -- at' );
    foreach ($at_job->queue() as $job) {
      $job->id;
      $job->start;
      $job->q;
      $job->user;
      $job->body();
    }

```
### Cancel Jobs

```php
<?php
    use SystemUtil\AtJobs;
    $at_job = new AtJobs( 'ssh server docker exec ubuntu1604 -- at' );
    foreach ($at_job->queue('a') as $job) {
      $job->remove();
    }
```
    