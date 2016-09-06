<?php
abstract class Logger
{
  protected static
    $modified = null,
    $old = null;

  public static function prepareLog(Doctrine_Record $object, $pk = 'id')
  {
    self::$modified = $object->getModified();
    if ($object->$pk)
    {
      $class = get_class($object);
      $data = $object->getTable()->createQuery()
        ->andWhere("$pk = ?", $object->$pk)
        ->fetchOne(array(), Doctrine::HYDRATE_ARRAY);
      self::$old = new $class();
      self::$old->fromArray($data);
    }
  }

  public static function log(Doctrine_Record $object, array $fields = null, $pk = 'id')
  {
    if (empty(self::$modified) || !$object->$pk)
    {
      self::$modified = self::$old = null;
      return;
    }

    if (is_null($fields))
    {
      $fields = array_keys(self::$modified);
    }


    foreach ($fields as $key => $field)
    {
      if (is_numeric($key))
      {
        $getter =  'get'.sfInflector::camelize($key);
      }
      else
      {
        $getter =  'get'.sfInflector::camelize($field);
        $field = $key;
      }

      if (!self::$old || self::$old->$getter() != $object->$getter())
      {
        $log = new Log();
        $log->model = get_class($object);
        $log->pk = $object->$pk;
        $log->field = $field;
        $log->old = self::$old ? (string) self::$old->$getter() : null;
        $log->new = (string) $object->$getter();
        $log->created_at = date('Y-m-d H:i:s');
        $log->save();
      }
    }

    self::$modified = self::$old = null;
    return true;
  }
}
