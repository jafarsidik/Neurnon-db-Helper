<?php
namespace Neuron\Generic;

class Error extends Result {

  protected $_level;
  protected $_trace = array();

  public function __construct($guid = 0, $code = self::CODE_SUCCESS, $info = self::INFO_SUCCESS, $owner = null, $level = E_USER_NOTICE) {

    $this->_level = $level;
    parent::__construct($guid, $code, $info);
    parent::extend('owner', $owner);
  }

  public function clear() {

    parent::clear();
    $this->owner = null;
    $this->_trace = array();
  }

  public function trace($text) {

    if (is_array($text)) {
      foreach ($text as $item) {
        $this->_trace[] = $item;
      }
    } else {
      $this->_trace[] = $text;
    }
  }

  public function merge(Error $error) {

    $data = $error->toArray();
    $count = 0;

    if (count($data['trace']) > 0) {
      foreach ($data['trace'] as $trace) {
        $this->trace($trace);
        $count++;
      }
    }

    if ($data['info']) {
      $this->trace($data['info']);
      $count++;
    }

    return $count;
  }

  public function display($html = true) {

    if ($html) { echo('<pre>'); }
    echo($this->toString() . "\n");
    if ($html) { echo('</pre>'); }
    return true;
  }

  public function log() {

    return error_log($this->toString());
  }

  public function trigger() {

    return trigger_error($this->toString(), $this->_level);
  }

  public function toString() {

    $out = 'Trace for owner ' . ($this->owner ? $this->owner : 'global') . ' at ' . date('Y-m-d H:i:s') . " {\n" .
           '  Code #' . $this->code . ': ' . $this->info . "\n";
    foreach ($this->_trace as $text) {
      $out .= '  : ' . $text . "\n";
    }
    $out .= "}";

    return $out;
  }

  public function toArray() {

    return array_merge(parent::toArray(), array(
      'owner' => $this->owner,
      'trace' => $this->_trace,
    ));
  }

  public function toJson() {

    return json_encode($this->toArray());
  }
}