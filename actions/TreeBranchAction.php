<?php

class TreeBranchAction extends TreeAction
{
    public function run($id)
    {
        $model = is_numeric($id)
            ? $this->controller->getModel($id)
            : $this->controller->getDefaultModel();
        $this->printNode($model);
    }
}