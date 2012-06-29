<?
if($config['enabled'])
{
  error_reporting(E_ALL | E_STRICT);
  ini_set('display_errors', 'stdout');
}

require_once('DebugMixin.class.php');
W::add_mixin('DebugMixin');
