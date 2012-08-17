<?

class DebugMixin extends Mixin
{
  static $is_cli = false;
    
  static function debug_shutdown_handler() {
    $error = error_get_last();
    if($error !== NULL){
        $info = "[SHUTDOWN] file:".$error['file']." | ln:".$error['line']." | msg:".$error['message'] .PHP_EOL;
        self::dprint($info);
    }
  }
  
  // error handler function
  static function debug_error_handler($errno, $errstr, $errfile, $errline)
  {
    $mode = 'development';
    switch($mode)
    {
      case 'development':
        self::dprint($errstr);
        die;
        break;
      case 'staging':
      case 'production':
        $key = md5("$errno|$errstr|$errfile|$errline");
        $err = ErrorLog::find_or_create_by( array(
          'conditions'=>array('token = ?', $key),
          'attributes'=>array(
            'code'=>$errno,
            'line_number'=>$errline,
            'fpath'=>$errfile,
            'message'=>$errstr,
            'token'=>$key,
            'backtrace'=>debug_backtrace()
          )
        ));
        $err->count++;
        $err->save();
        break;
    }
  }
  
  static function debug_exception_handler($exception) {
    self::dprint( "Uncaught exception: " , $exception->getMessage(), "\n");
  }
  
  
  static function dprint($s,$shouldExit=true)
  {
    if(!self::$is_cli) echo "\"><pre>";
    ob_start();
    var_dump($s);
    $out = ob_get_contents();
    ob_end_clean();
    if(self::$is_cli)
    {
      echo $out;
    } else {
      echo htmlentities($out,ENT_COMPAT,'UTF-8');
    }
    if(!self::$is_cli) echo "</pre>";
    if ($shouldExit) self::error('Development stop');
  }
  
  
  static function error($err, $data=null)
  {
    if ($data)
    {
      if(self::$is_cli)
      {
        $err .= W::s_var_export($data);
      } else {
        $err = $err."<br/><pre>".htmlentities(W::s_var_export($data))."</pre>";
      }
    }
    if(!self::$is_cli)
    {
      echo( "\"><table>");
      echo ("<tr>");
      echo("<td>");
      echo($err);
      echo("</td>");
      echo("</tr>");
    }
    foreach(debug_backtrace() as $trace)
    {
      if(!self::$is_cli)
      {
        echo( "<tr>");
        echo( "<td>");
      }
      if (array_key_exists('file', $trace)) echo( htmlentities($trace['file']));
      if(!self::$is_cli)
      {
        echo( "</td>");
        echo( "<td>");
      } else {
        echo "\t";
      }
      if (array_key_exists('line', $trace)) echo( htmlentities($trace['line']));
      if(!self::$is_cli)
      {
        echo( "</td>");
        echo( "<td>");
      } else {
        echo "\t";
      }
      if (array_key_exists('static function', $trace)) echo( htmlentities($trace['static function']));
      if(!self::$is_cli)
      {
        echo( "</td>");
        echo( "</tr>");
      } else {
        echo "\n";
      }
    }
    if(!self::$is_cli) echo( "</table>");
    die;
    //trigger_error($err, E_USER_ERROR);
  }
  
  static function s_var_export($v)
  {
    ob_start();
    var_export($v);
    $s = ob_get_contents();
    ob_end_clean();
    return $s;
  }
}