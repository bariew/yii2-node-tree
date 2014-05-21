<?php

class TreeAction extends CAction
{
    protected function printNode($node)
    {
        header('Content-type: application/json');
        echo CJavaScript::jsonEncode(array($node->tree->getBranch()));
        Yii::app()->end();
    }
    
    protected function printTree($node)
    {
        header('Content-type: application/json');
        echo CJavaScript::jsonEncode(array($node->tree->getJsTree()));
        Yii::app()->end();
    }
}