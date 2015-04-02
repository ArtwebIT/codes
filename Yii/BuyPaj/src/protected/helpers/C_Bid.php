<?php

class C_Bid
{

    /**
     * Получаем общее кол-во (сумму) имеющихся паев в заявках по конкретному статусу (продают или покупают)
     *
     * @param $project_id - ID проекта
     * @param $status - тип сделки (0-продают, 1-покупают)
     */
    public static function getBidSumCountPay($project_id, $status)
    {
        if (isset($project_id) and !empty($project_id))
        {
            // Общее кол-во паев уже в заявках
            $criteria = new CDbCriteria;
            $criteria->select = 'SUM(`count`) as bid_count_pay';
            $criteria->condition = 'status = :status AND project_id = :projectID';
            $criteria->params = array(':status' => $status, ':projectID' => (int)$project_id);
            $modelBid = Bid::model()->find($criteria);
            if ($modelBid)
            {
                return $modelBid->bid_count_pay;
            }
        }
    }


    /**
     * Получаем кол-во доступных паев для дальнейшей покупки
     * для проекта типа "Возможно приобретение"
     * и по статусу заявки "покупка"
     *
     * @param $project_id - ID проекта
     * @param $bid_status - тип сделки (0-продают, 1-покупают)
     */
    public static function getFreeCountPay($project_id)
    {
        if (isset($project_id) and !empty($project_id))
        {
            $sql = '
            SELECT SUM(b.`count`) AS `bid_count_pay`, p.`count_pay` AS `project_count_pay`
            FROM {{bid}} AS b
            INNER JOIN {{projects}} AS p
            ON b.`project_id` = p.`id`
            WHERE b.`status` = '. Bid::BID_STATUS_BUY .
            ' AND b.`project_id` = '. $project_id .
            ' AND p.`type` = ' . Projects::TYPE_2
            ;
            // WHERE b.`status` = '. Bid::BID_STATUS_BUY .
            $connection = Yii::app()->db;
            $modelBid = $connection->createCommand($sql)->queryRow();
            $free_count_pay = 0;

            // если заявки есть
            if ($modelBid)
            {
                if (isset($modelBid['project_count_pay']) and !empty($modelBid['project_count_pay']))
                {
                    $free_count_pay = $modelBid['project_count_pay'];
                    if (isset($modelBid['bid_count_pay']) and !empty($modelBid['bid_count_pay']))
                    {
                        $free_count_pay = $free_count_pay - $modelBid['bid_count_pay'];
                    }

                    return $free_count_pay;
                }
                // если заявок еще нет
                else
                {
                    $modelProject = Projects::model()->findByPk($project_id);
                    $free_count_pay = $modelProject->count_pay;

                    return $free_count_pay;
                }
            }
        }
    }
}