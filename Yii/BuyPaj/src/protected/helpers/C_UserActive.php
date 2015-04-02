<?php

class C_UserActive
{
    /**
     * Обновляем актив пользователя
     *
     * @param $user_id
     * @param $project_id
     * @param $count_pay - кол-во паев
     * @param $total_pay - сумма актива
     * @return bool
     */
    public static function addActive($user_id, $project_id, $count_pay, $total_pay)
    {
        if (isset($user_id) and !empty($user_id))
        {
            $exists = UserActive::model()->exists(
                'user_id = :userID AND project_id = :projectID',
                array(':userID' => $user_id, ':projectID' => $project_id)
            );

            if ($exists)
            {
                $int = UserActive::model()->updateCounters(
                    array(
                        'count_pay' => (int)$count_pay,
                        'total_pay' => (float)$total_pay,
                    ),
                    'user_id = :userID AND project_id = :projectID',
                    array(':userID' => (int)$user_id, ':projectID' => (int)$project_id)
                );
                if ($int)
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_INFO, $user_id, 'Обновили актив пользователя. Добавили кол-во паев: ' . $count_pay . '. Сумма покупки: ' . $total_pay);

                    return true;
                }
                else
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_ERROR, $user_id, 'addActive. Ошибка обновления.');

                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Ошибка обновления.'
                    ));
                    Yii::app()->end();
                }
            }
            else
            {
                $UserActive = new UserActive;
                $UserActive->user_id = $user_id;
                $UserActive->project_id = $project_id;
                $UserActive->count_pay = $count_pay;
                $UserActive->total_pay = $total_pay;

                if ($UserActive->save())
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_INFO, $user_id, 'Добавиил актив пользователя. Добавили кол-во паев: ' . $count_pay . '. Сумма покупки: ' . $total_pay);

                    return true;
                }

            }
        }
    }
}