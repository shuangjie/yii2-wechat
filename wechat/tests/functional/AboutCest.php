<?php
namespace wechat\tests\functional;

use wechat\tests\FunctionalTester;

class AboutCest
{
    public function checkAbout(FunctionalTester $I)
    {
        $I->amOnRoute('site/about');
        $I->see('About', 'h1');
    }
}
