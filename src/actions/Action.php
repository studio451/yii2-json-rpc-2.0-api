<?

namespace studio451\yii2jsonrpc2api\actions;

use yii\base\InvalidConfigException;
use studio451\yii2jsonrpc2api\Exception;

class Action extends \yii\base\Action
{
    public $modelClass;
    
    public $findModel;

    public $checkAccess;


    public function init()
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException(get_class($this) . '::$modelClass must be set.');
        }
    }

    public function findModel($id)
    {
        if ($this->findModel !== null) {
            return call_user_func($this->findModel, $id, $this);
        }

        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne($id);
        }

        if (isset($model)) {
            return $model;
        }

        throw new Exception("Object not found: $id");
    }
}
