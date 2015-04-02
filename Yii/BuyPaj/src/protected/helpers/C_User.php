<?php

class C_User
{

    /**
     * Получаем email супер-админа (root)
     */
    public static function getEmailSuperAdmin()
    {
        $user = User::model()->find('superuser = :superuser', array(':superuser' => 1));
        if ($user)
        {
            return $user->email;
        }
    }


    /**
     * Получаем баланс пользователя
     *
     * @return array - полная инфа о балансе (весь баланс, заблокироанный, доступный)
     */
    public static function getUserBalans()
    {
        $model = Balans::model()->find('user_id=:userID', array(':userID' => Yii::app()->user->id));

        $user_balans            = 0;
        $user_blocked_balans    = 0;
        $user_allow_balans      = 0;

        if ($model)
        {
            if (isset($model->sum) and !empty($model->sum))
                $user_balans = (float)$model->sum;

            if (isset($model->blocked_sum) and !empty($model->blocked_sum))
                $user_blocked_balans = (float)$model->blocked_sum;

            $user_allow_balans = $user_balans - $user_blocked_balans;
        }

        return array(
            'user_balans'            => $user_balans,
            'user_blocked_balans'    => $user_blocked_balans,
            'user_allow_balans'      => $user_allow_balans,
        );
    }


    /**
     * Пополнение/снятие денег (баланса)
     *
     * @param null $user_id - ID пользователя
     * @param null $sum - сумма пополнения/снятие
     * @param bool $minus - если true, то отнимаем
     * @param bool $blocked - если true, то это заблокированные средства
     */
    public static function updateMoney($user_id, $sum = 0, $minus, $blocked)
    {
        if (
            (isset($user_id) and !empty($user_id))
            and ($sum > 0)
        )
        {
            // если отнимаем или уменьшаем
            if ($minus == true)
                $sum = -1 * $sum;

            $balans = Balans::model()->exists('user_id = :userID', array(':userID' => $user_id));
            if ($balans)
            {
                // обновляем заблокированные средства
                if ($blocked)
                {
                    $int = Balans::model()->updateCounters(
                        array('blocked_sum' => $sum),
                        'user_id = :userID',
                        array(':userID' => $user_id)
                    );
                    if ($int)
                    {
                        // Логирование в БД
                        C_Log::addLog(Log::TYPE_INFO, $user_id, 'Обновили заблокированные средства: ' . $sum);

                        return true;
                    }
                    else
                    {
                        // Логирование в БД
                        C_Log::addLog(Log::TYPE_ERROR, $user_id, 'Баланс. Заблокированные средства не обновлены');
                        return false;
                    }
                }
                else // обновляем обычные средства
                {
                    $int = Balans::model()->updateCounters(
                        array('sum' => $sum),
                        'user_id = :userID',
                        array(':userID' => $user_id)
                    );
                    if ($int)
                    {
                        // Логирование в БД
                        C_Log::addLog(Log::TYPE_INFO, $user_id, 'Обновили обычные средства: ' . $sum);

                        return true;
                    }
                    else
                    {
                        // Логирование в БД
                        C_Log::addLog(Log::TYPE_ERROR, $user_id, 'Баланс. Обычные средства не обновлены');
                    }
                }
            }
            // если записи нет в БД то создаем
            else
            {
                $int                = new Balans;
                $int->sum           = $sum;
                $int->user_id       = $user_id;
                $int->blocked_sum   = 0;

                if ($int->save())
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_INFO, $user_id, 'Баланс. Добавлены средства: ' . $sum);
                    return true;
                }
                else
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_ERROR, $user_id, 'Баланс. Ошибка сохранения баланса пользователя');
                    return false;
                }

            }
        }
        else
        {
            // Логирование в БД
            C_Log::addLog(Log::TYPE_ERROR, $user_id, 'Баланс. Ошибка входных параметров');

            return false;
        }
    }


    /**
     * Проверяем баланс пользователя (есть ли столько средств)
     *
     * @param null $user_id - ID пользователя
     * @param int $sum - сумма, наличие которой проверяем на балансе у пользователя
     */
    public static function checkMoneyOnBalans($user_id = null, $sum = 0)
    {
        if (
            (isset($user_id) and !empty($user_id))
            and ($sum > 0)
        )
        {
            $model = Balans::model()->find('user_id = :userID', array(':userID' => $user_id));
            if ($model)
            {
                $user_balans            = 0;
                $user_blocked_balans    = 0;
                $user_allow_balans      = 0;

                if (isset($model->sum) and !empty($model->sum))
                    $user_balans = (float)$model->sum;

                if (isset($model->blocked_sum) and !empty($model->blocked_sum))
                    $user_blocked_balans = (float)$model->blocked_sum;

                $user_allow_balans = $user_balans - $user_blocked_balans;

                // если у пользователя есть нужное кол-во средств
                // если средств достаточно
                if ($sum < $user_allow_balans)
                {
                    return true;
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
        else
        {
            return false;
        }
    }


    /**
     * Проверяем, есть ли у пользователя столько паев
     *
     * @param null $user_id - user ID
     * @param null $project_id - Project ID
     * @param int $count_pay - Кол-во проверяемых паев
     */
    public static function checkUserActive($user_id = null, $project_id = null, $count_pay = 0)
    {
        if (
            (isset($user_id) and !empty($user_id))
            and ($count_pay > 0)
        )
        {
            $model = UserActive::model()->find('user_id = :userID AND project_id = :projectID', array(':userID' => $user_id, ':projectID' => $project_id));

            if (!$model or ($model->count_pay == 0))
            {
                echo CJSON::encode(array(
                    'status' => 201,
                    'message' => 'Вы не можете продать то, чего у вас нет.'
                ));
                Yii::app()->end();
            }

            $criteria = new CDbCriteria;
            $criteria->select = 'SUM(count) as count';
            $criteria->condition = 'project_id = :projectID AND user_id = :userID AND status = :status';
            $criteria->params = array(':projectID' => $project_id, ':userID' => $user_id, ':status' => Bid::BID_STATUS_SALE);
            $modelBid = Bid::model()->find($criteria);

            $sum_all_bid = 0;
            if (isset($modelBid->count) and !empty($modelBid->count))
            {
                if ($modelBid->count > 0)
                {
                    $sum_all_bid = $modelBid->count + $count_pay;
                }
            }
            else
            {
                $sum_all_bid = $count_pay;
            }

            if ($sum_all_bid > $model->count_pay)
            {
                echo CJSON::encode(array(
                    'status' => 201,
                    'message' => 'У вас нет столько паев. Укажите меньшее количество паев.'
                ));
                Yii::app()->end();
            }

            return true;
        }
    }


    /**
     * Начисляем дивиденды пользователю
     *
     * @param $user_id
     * @param $sum
     */
    public static function plusDividends($user_id, $project_id, $sum)
    {
        if (
            (isset($user_id) and !empty($user_id))
            and ($sum > 0)
        )
        {
            $exists = Balans::model()->exists(
                'user_id = :userID',
                array(':userID' => $user_id)
            );

            if ($exists)
            {
                Balans::model()->updateCounters(
                    array('sum' => $sum),
                    'user_id = :userID',
                    array(':userID' => $user_id)
                );
            }
            else
            {
                $balans = new Balans;
                $balans->sum = $sum;
                $balans->user_id = $user_id;
                $balans->save();
            }

            $project = Projects::model()->findByPk($project_id);
            $project_name = $project->name;

            // заносим инфу в историю баланса
            $model = new BalansHistory;
            $model->user_id = $user_id;
            $model->project_id = $project_id;
            $model->operation = BalansHistory::OPERATION_DIVIDENDS;
            $model->sum = (float)$sum;
            $model->notes = $project_name;

            if ($model->save())
            {
                return true;
            }
        }
    }


    /**
     * @param $user_id
     * @param null $project_id
     * @param $operation
     * @param $sum
     * @param null $notes
     * @return bool
     */
    public static function addBalansHistory($user_id, $project_id = null, $operation, $sum, $notes = null, $buy_system = null)
    {
        if ($notes == null)
        {
            $modelUser = User::model()->with('profile')->findByPk($user_id);
            if ($buy_system != null)
            {
                // получаем поле
                $field = Yii::app()->params['short_buy_systems'][$buy_system];
                // проверяем значение в этом поле
                if (isset($modelUser->profile[$field]) and !empty($modelUser->profile[$field]))
                {
                    $notes = $modelUser->profile[$field];
                }
            }
        }

        $model = new BalansHistory;
        $model->user_id     = (int)$user_id;
        $model->project_id  = (int)$project_id;
        $model->operation   = $operation;
        $model->sum         = (float)$sum;
        $model->notes       = $notes;

        if ($model->save())
        {
            return true;
        }
    }


    /**
     * Получаем значение поля платежной системы
     *
     * @param $user_id - userID
     * @param $buy_system - платежная система (1 - webmoney, 2 - PerfectMoney, 3 - OnlyMoney)
     * @return mixed
     */
    public static function getUserBuySystem($user_id, $buy_system)
    {
        $model = User::model()->with('profile')->findByPk($user_id);

        if (isset($buy_system) and !empty($buy_system))
        {
            // получаем поле
            $field = Yii::app()->params['short_buy_systems'][$buy_system];

            // проверяем значение в этом поле
            if (isset($model->profile[$field]) and !empty($model->profile[$field]))
            {
                return $model->profile[$field];
            }
            else
            {
                return false;
            }
        }
    }
}