<?php

class TreeCreateAction extends TreeAction
{
    public function run($id)
    {
        $model = $this->controller->getModel();
        $model->attributes = $_POST['attributes'];
        if($model->tree->move($id)){
            echo json_encode($model->tree->nodeAttributes());
        }
    }
}