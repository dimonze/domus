<?php

/**
 * Base project form.
 * 
 * @package    domus
 * @subpackage form
 * @author     Garin Studio 
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class BaseForm extends sfFormSymfony
{
  public static $TinyMCEConfig = 'language : "ru",
              plugins: "filemanager,table,contextmenu,paste",
              theme_advanced_buttons1: "formatselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink,|,cleanup,code",
              theme_advanced_buttons2: "sub,sup,pastetext,pasteword,visualaid,removeformat,charmap,hr,|,image,filemanager_images,filemanager_files,|,tablecontrols",
              theme_advanced_buttons3: ""';
}
