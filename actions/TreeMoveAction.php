<?php

namespace bariew\nodeTree\actions;

class TreeMoveAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->findModel($id);
        if(!$model->getBehavior('nodeTree')->move($_POST['pid'], $_POST['position'])){
            throw new \yii\web\HttpException(400, "Could not save changes");
        }
    }
}