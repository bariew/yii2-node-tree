<?php

class ARTreeMenuWidget extends CWidget
{
    public $items;
    public $behavior; // ARTreeBehavior instance
	public $view = 'admin';
    
    public function run()
    {
		if(!$items = $this->items){
            return;
        }
        $jsTreePath = Yii::app()->assetManager->publish(
            dirname(__FILE__).DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."jsTree");
        Yii::app()->clientScript
            ->registerCssFile($jsTreePath.'/src/themes/default/style.css')
            ->registerScriptFile($jsTreePath.'/src/jstree.js')
            ->registerScriptFile($jsTreePath.'/src/jstree.dnd.js')
            ->registerScriptFile($jsTreePath.'/src/jstree.search.js')
            ->registerScriptFile($jsTreePath.'/src/jstree.types.js')
            ->registerScriptFile($jsTreePath.'/src/jstree.state.js')
            ->registerScriptFile($jsTreePath.'/src/jstree.contextmenu.js')
            ->registerScriptFile($jsTreePath.'/artree.js', CClientScript::POS_END)
        ;
		$this->render($this->view, compact('items'));
    }
}