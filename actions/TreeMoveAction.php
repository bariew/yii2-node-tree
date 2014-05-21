<?php

class TreeMoveAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->getModel($id);
        if(!$model->tree->move($_POST['pid'], $_POST['position'])){
            throw new CHttpException(400, "Could not save changes");
        };
    }
}