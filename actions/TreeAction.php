<?php
namespace bariew\nodeTree\actions;
use yii\base\Action;

class TreeAction extends Action
{
    protected function printNode($node)
    {
        header('Content-type: application/json');
        echo json_encode(array($node->getBehavior('nodeTree')->getBranch()));
        \Yii::$app->end();
    }
    
    protected function printTree($node)
    {
        header('Content-type: application/json');
        echo json_encode(array($node->getBehavior('nodeTree')->getJsTree()));
        \Yii::$app->end();
    }
}