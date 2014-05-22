<?php

namespace bariew\nodeTree;

class ARTreeMenuWidget extends \yii\base\Widget
{
    public $items;
    public $behavior; // ARTreeBehavior instance
    public $view = 'node';
    
    public function run()
    {
        if(!$items = $this->items){
            return;
        }
        $behavior = $this->behavior;
        ARTreeAssets::register($this->getView());
        return $this->render($this->view, compact('items', 'behavior'));
    }
}