<?php

class HeirRemoveAction extends TreeAction
{
    public function run($id, $ancestor_id)
    {
        $model = $this->controller->getModel($id);
        $model->treeInheritage->unlegate($ancestor_id);
        $model->save(false);
    }
}