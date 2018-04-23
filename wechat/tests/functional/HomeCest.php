<?php

namespace wechat\tests\functional;

use wechat\tests\FunctionalTester;

class HomeCest
{
    public function checkOpen(FunctionalTester $I)
    {
        $I->amOnPage(\Yii::$app->homeUrl);
        $I->see('My Application');
        $I->seeLink('About');
        $I->click('About');
        $I->see('This is the About page.');
    }
}