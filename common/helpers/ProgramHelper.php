<?php
namespace common\helpers;

use common\models\Client;
use common\models\Expense;
use common\models\Payment;
use common\models\Program;
use common\models\ProgramClient;
use common\models\ProgramFamily;
use common\models\ProgramGuide;
use common\models\ProgramPrice;
use common\models\ProgramUser;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\StaleObjectException;

/**
 * Helper functionality for Program model.
 *
 * @author i
 *
 */
class ProgramHelper
{
    public static function getStatus()
    {
        return [
            1 => Yii::t('app', 'Urgent Problem'),
            2 => Yii::t('app', 'Problem'),
            3 => Yii::t('app', 'Requires Attention'),
            4 => Yii::t('app', 'Waiting'),
            5 => Yii::t('app', 'Processing'),
            6 => Yii::t('app', 'Almost Done'),
            7 => Yii::t('app', 'All Ready'),
        ];
    }

    /**
     * Returns an array with the short version of the status.
     * The string returned by this method are suitable for post-processing,
     * for example they can be used to set the class of an HTML element.
     *
     * @return string[]
     */
    public static function getShortStatus()
    {
        return [
            1 => 'urgent-problem',
            2 => 'problem',
            3 => 'requires-attention',
            4 => 'waiting',
            5 => 'processing',
            6 => 'almost-done',
            7 => 'all-ready',
        ];
    }

    /**
     * Return all the possible names of the periods that a program can
     * have inside a program group.
     *
     * @return string[]
     */
    public static function getProgramPeriods()
    {
        return [
            '一期',
            '二期',
            '三期',
            '四期',
            '五期',
            '六期',
            '七期',
            '八期',
            '九期',
            '十期',
            '加期',
        ];
    }

    /**
     * Returns a localized and formatted version of the program dates.
     *
     * @param $program
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDateString($program)
    {

        if (empty($program->start_date)) {

            if (empty($program->end_date)) {

                // If both dates are empty return an empty string
                return '';

            }

            // If only the end date has a value return the end date
            return Yii::$app->formatter->asDate($program->end_date);

        }

        if (empty($program->end_date)) {

            // If only the start date has a value return the start date
            return Yii::$app->formatter->asDate($program->start_date);

        }

        // Both start date and end date are not null

        // Same year but different month
        if (Yii::$app->language === 'zh-CN') {

            $start = Yii::$app->formatter->asDate($program->start_date, 'yy年MM月dd日');

        } else {

            $start = Yii::$app->formatter->asDate($program->end_date, 'php:Y M jS');

        }

        // $start = Yii::$app->formatter->asDate($program->start_date, 'yy年MM月dd日');

        if (Yii::$app->formatter->asDate($program->start_date, 'php:Y') !==
            Yii::$app->formatter->asDate($program->end_date, 'php:Y')) {

            // Different year, display full date
            $end = Yii::$app->formatter->asDate($program->end_date);

        } elseif (Yii::$app->formatter->asDate($program->start_date, 'php:m') !==
            Yii::$app->formatter->asDate($program->end_date, 'php:m')) {

            // Same year but different month
            if (Yii::$app->language === 'zh-CN') {

                $end = Yii::$app->formatter->asDate($program->end_date, 'php:m月d日');

            } else {

                $end = Yii::$app->formatter->asDate($program->end_date, 'php:M jS');

            }

        } elseif (Yii::$app->formatter->asDate($program->start_date, 'php:d') !==
            Yii::$app->formatter->asDate($program->end_date, 'php:d')) {

            // Same year and month but different day
            if (Yii::$app->language === 'zh-CN') {

                $end = Yii::$app->formatter->asDate($program->end_date, 'php:d日');

            } else {

                $end = Yii::$app->formatter->asDate($program->end_date, 'php:jS');

            }

        } else {

            $end = null;

        }

        if ($end === null) {

            return $start;

        }

        return Yii::t('app', '{start} to {end}', [
            'start' => $start,
            'end' => $end,
        ]);
    }

    /**
     * Record that the current application user just viewed this program.
     *
     * @param $program
     */
    public static function markUserViewedProgram ($program)
    {
        $user = User::findOne(Yii::$app->user->id);

        // Only possible to mark if the application has a current user
        if ($user !== null) {

            $programUser = ProgramUser::findOne([
                'program_id' => $program->id,
                'user_id' => $user->id
            ]);

            if ($programUser === null) {

                // If we can't find the record, create it
                $programUser = new ProgramUser();
                $programUser->user_id = $user->id;
                $programUser->program_id = $program->id;
                $programUser->last_viewed_at = time();

                if (!$programUser->save()) {
                    Yii::error(
                        "Problem saving new ProgramUser program $program->id, user $user->id",
                        __METHOD__);
                }

            } else {
                /* @var yii\behaviors\TimestampBehavior $programUser */
                $programUser->touch('last_viewed_at');
            }

        }
    }

    /**
     * Check if the program/index entry corresponding to the Program requires
     * a highlight CSS class.
     *
     * @param $program
     * @return string
     */
    public static function getProgramIndexGridItemHighlightClass ($program)
    {
        // Check if it has unanswered questions
        $hasUnanswered = false;

        foreach ($program->programGroup->qas as $qa) {

            if (empty(trim($qa->answer))) {
                $hasUnanswered = true;
            }

        }

        // Always highlight programs with unanswered questions
        if ($hasUnanswered) {
            return 'program-index-has-unanswered';
        }

        // Ignore programs older than a month
        if ($program->updated_at < strtotime('-1 month')) {
            return '';
        }

        // If the program does not have unanswered questions check the program_user
        $user = User::findOne(Yii::$app->user->id);

        // Only possible to mark if the application has a current user
        if ($user !== null) {

            $programUser = ProgramUser::findOne([
                'program_id' => $program->id,
                'user_id' => $user->id
            ]);

            if ($programUser === null) {
                return 'program-index-has-update';
            }

            if (empty($programUser->last_viewed_at)) {
                return 'program-index-has-update';
            }

            if ($program->updated_at > $programUser->last_viewed_at) {
                return 'program-index-has-update';
            }

        }

        return '';

    }

    /**
     * Refresh the updated_at timestamp in the programs this client participates in
     * to reflect that there has been a change in the information.
     *
     * @param Client $client
     */
    public static function markClientProgramAsUpdated ($client): void
    {
        foreach ($client->programs as $program) {

            // Only update "current" programs
            if (strtotime($program->end_date) > strtotime('-1 month')) {
                /* @var $program TimestampBehavior  silence warning */
                $program->touch('updated_at');
            }

        }
    }

    /**
     * Delete all related records that could make program deletion fail.
     * For each related record, recursively delete related records that
     * reference them.
     *
     * https://stackoverflow.com/a/1133461/2557030
     * select constraint_name,table_name,column_name
     *   from information_schema.key_column_usage
     *   where referenced_table_name='program'
     *   and referenced_column_name='id'
     *   and table_schema='database_name_here';
     *
     * @param Program $program
     * @return void
     * @throws StaleObjectException
     * @throws \Throwable
     */
    public static function deleteAllRelatedRecords (Program $program): void
    {
        Expense::deleteAll(['program_id' => $program->id]);
        Payment::deleteAll(['program_id' => $program->id]);
        ProgramClient::deleteAll(['program_id' => $program->id]);
        ProgramFamily::deleteAll(['program_id' => $program->id]);
        ProgramGuide::deleteAll(['program_id' => $program->id]);
        ProgramUser::deleteAll(['program_id' => $program->id]);
        // Call delete on each item to insure beforeDelete gets called
        foreach ($program->getProgramPrices()->each() as $programPrice) {
            /** @var ProgramPrice $programPrice */
            $programPrice->delete();
        }
    }

    /**
     * Fill the deposit and deposit message with some defaults if empty
     * @param Program $program
     */
    public static function fillDefaultDepositValues (Program $program)
    {
        if ($program->deposit === null || $program->deposit === '') {
            if ($program->programGroup->location->international) {
                $program->deposit = 5000;
            } else {
                $program->deposit = 2000;
            }
        }
        if (empty($program->deposit_message)) {
            $program->deposit_message = '此购买链接用于支付活动定金，' . $program->deposit .
                '元/人，活动费用以页面显示金额为准，工作人员会联系您补缴尾款';
        }
    }
}
