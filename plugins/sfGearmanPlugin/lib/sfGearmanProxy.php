<?php

class sfGearmanProxy
{
  protected static $_clients;

  public static function __callStatic($method, $args)
  {
    if (0 === strpos($method, 'do')) {
      $client = self::getClient();
      $args[0] = $client->getMethodName($args[0]);
      if (!empty($args[1]) && !is_scalar($args[1])) {
        $args[1] = serialize($args[1]);
      }
      return call_user_func_array(array($client, $method), $args);
    }

    throw new Exception('Failed to proxy method: ' . $method);
  }

  public static function getClient()
  {
    if (null === self::$_clients) {
      self::$_clients = array();
      foreach (explode(',', sfConfig::get('app_gearman_servers', '127.0.0.1:4730')) as $server) {
        self::$_clients[$server] = new sfGearmanClient($server);
      }
    }

    return self::$_clients[array_rand(self::$_clients)];
  }
}

//public string doBackground ( string $function_name , string $workload [, string $unique ] )
//public string doHigh ( string $function_name , string $workload [, string $unique ] )
//public string doHighBackground ( string $function_name , string $workload [, string $unique ] )
//public string doJobHandle ( void )
//public string doLow ( string $function_name , string $workload [, string $unique ] )
//public string doLowBackground ( string $function_name , string $workload [, string $unique ] )
//public string doNormal ( string $function_name , string $workload [, string $unique ] )
