<?php
class filemanagerconnectorActions extends filemanagerconnectorBaseActions {

  public function __construct($context, $moduleName, $actionName) {
    self::$base_path = sfConfig::get('sf_upload_dir') . '/content';
    parent::__construct($context, $moduleName, $actionName);
  }

  protected function auth(sfWebRequest $request) {
    return false;
  }
}
