<?

namespace studio451\yii2jsonrpc2api\actions;

use Yii;
use yii\base\Model;
use studio451\yii2jsonrpc2api\Exception;

class UpdateAction extends Action
{
    public $scenario = Model::SCENARIO_DEFAULT;

    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $model->scenario = $this->scenario;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new Exception('Failed to update the object for unknown reason.');
        }

        return $model;
    }
}
