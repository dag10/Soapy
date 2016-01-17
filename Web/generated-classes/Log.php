<?php

use Base\Log as BaseLog;

/**
 * Skeleton subclass for representing a row from the 'log' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class Log extends BaseLog
{
  public static function CreateErrorLog($bathroom, $msg, $data=NULL) {
    $log = new Log();

    $log->setTime(date('Y-m-d H:i:s', time()));
    $log->setLevel('ERROR');
    $log->setTag('Server');
    $log->setBathroom($bathroom);

    $msg = "Failed to add log event. Error message: \"$msg\"";
    if ($data) {
      $msg .= " Data: \"" . var_export($data, true) . "\"";
    }

    $log->setMessage($msg);

    return $log;
  }

  public static function CreateLog($data) {
    $log = new Log();

    try {
      $log->setTime($data['time']);
      $log->setMessage($data['message']);

      if (isset($data['level'])) {
        $log->setLevel($data['level']);
      }

      if (isset($data['tag'])) {
        $log->setTag($data['tag']);
      }

      if (isset($data['bathroom'])) {
        $log->setBathroom($data['bathroom']);
      }
    } catch (Exception $e) {
      $log = Log::CreateErrorLog(
        isset($data['bathroom']) ? $data['bathroom'] : NULL, $e->getMessage(),
        $data);
    }

    return $log;
  }
}
