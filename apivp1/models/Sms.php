<?php
/**
 * Created by PhpStorm.
 * User: xklxq
 * Date: 2020/9/11
 * Time: 11:32
 * Desc：
 */

namespace apivp1\models;


class Sms extends \common\models\Sms {
	public function scenarios() {
		$Scenario        = parent::scenarios(); // TODO: Change the autogenerated stub
		$Scenario['add'] = ['mobile', 'code'];

		return $Scenario;
	}
}