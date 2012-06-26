<?

if($config['enabled'])
{
  error_reporting(E_STRICT | E_ALL);
  ini_set('display_errors', 'On');
}

require_once('DebugMixin.class.php');
W::add_mixin('DebugMixin');
