<?php
namespace Shlink;

class Logger
{

  private $debug = false;
  private $info  = false;
  private $starttime;
  private $pid;
  private $log_file;

  function __construct( $filename = 'logs.log' )
  {
    $this->log_file = SHLINK_PATH . '/' . $filename;
  }

  public function start()
  {
    $this->starttime = time();
    $this->pid       = random_int(0, 9999999);
    $this->info( sprintf( "Start time: %s", date( 'c', $this->starttime ) ) );
  }

  public function end()
  {
    $endtime = time();
    $this->info( sprintf( "End time: %s", date( 'c', $endtime ) ) );
    $this->info( sprintf( "Run time: %s", $endtime - $this->starttime ) );
  }

  public function log( $message )
  {
    if( $this->debug ) echo $message;
    $message = sprintf("[%s] [%s] %s\n", $this->pid, date('c'), $message );
    file_put_contents($this->log_file, $message, FILE_APPEND);
  }

  public function info( $message )
  {
    if( $this->info ) echo $message;
    $message = sprintf("[%s] [%s] %s\n", $this->pid, date('c'), $message );
    file_put_contents($this->log_file, $message, FILE_APPEND); 
  }

}
