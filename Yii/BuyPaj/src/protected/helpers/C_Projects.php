<?php

class C_Projects
{
    /**
     * Обвновляем кол-во паев у проекта
     *
     * @param $project_id
     * @param $count_pay
     * @param $minus
     */
    public static function updateCountPay($project_id, $count_pay, $minus = false)
    {
        if (isset($project_id) and !empty($project_id))
        {
            $model = Projects::model()->findByPk($project_id);
            if ($model)
            {
                if ($model->count_pay)
                {
                    // если вычитаем
                    if ($minus == true)
                        $count_pay = -1 * $count_pay;

                    $int = Projects::model()->updateCounters(
                        array('count_pay' => $count_pay),
                        'id = :ID',
                        array(':ID' => $project_id)
                    );

                    if ($int)
                    {
                        // log
                        C_Log::addLog(Log::TYPE_INFO, null, 'Для проекта ' . $project_id  . ' обновили кол-во паев: ' . $count_pay);

                        return true;
                    }
                }
                else
                {
                    return false;
                }

            }
            else
            {
                return false;
            }
        }
    }


    /**
     * Получить название проекта
     *
     * @param $project_id
     */
    public static function getProjectName($project_id)
    {
        $model = Projects::model()->findByPk($project_id);
        if (isset($model->name) and !empty($model->name))
        {
            return $model->name;
        } else {
            return null;
        }

    }
}