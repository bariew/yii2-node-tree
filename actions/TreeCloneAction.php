<?php

class TreeCloneAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->getModel($id);
        if(!$model->tree->cloneTo($_POST['pid'])){
            throw new CHttpException(400, "Model not saved");
        }
    }
}