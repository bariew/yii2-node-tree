<?php

class TreeLegateAction extends TreeAction
{
    public function run($id)
    {
        $target = $this->controller->getModel($_POST['target']);
        $model = $this->controller->getModel($id);
        if(!$model->treeInheritage->legate($target)){
            throw new CHttpException(400, "Model not saved");
        }
        $this->printTree($target);
    }
}