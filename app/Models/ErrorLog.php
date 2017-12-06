<?php

namespace App\Models;


/**
 * This is the model class for table "{{%error_log}}".
 *
 * @property integer $id
 * @property string $info
 * @property string $file
 * @property integer $time
 */
class ErrorLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%error_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['info', 'file', 'time'], 'required'],
            [['time'], 'integer'],
            [['info'], 'string', 'max' => 255],
            [['file'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'info' => 'Info',
            'file' => 'File',
            'time' => 'Time',
        ];
    }
}
