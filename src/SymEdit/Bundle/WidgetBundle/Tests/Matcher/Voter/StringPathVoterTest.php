<?php

namespace SymEdit\Bundle\WidgetBundle\Tests\Matcher\Voter;

use SymEdit\Bundle\WidgetBundle\Matcher\Voter\StringPathVoter;
use SymEdit\Bundle\WidgetBundle\Model\Widget;
use SymEdit\Bundle\WidgetBundle\Tests\TestCase;

class StringPathVoterTest extends TestCase
{
    protected function createWidget()
    {
        return new Widget();
    }

    public function testConstruct()
    {
        $voter = new StringPathVoter('foo');
        $this->assertEquals('foo', $voter->getString());
    }

    public function testString()
    {
        $voter = new StringPathVoter('foo');
        $voter->setString('bar');
        $this->assertEquals('bar', $voter->getString());
    }

    /**
     * @dataProvider matchingPathsProvider
     */
    public function testIncludeOnly($association)
    {
        $voter = new StringPathVoter('/foo/bar');
        $widget = $this->createWidget();
        $widget->setVisibility(Widget::INCLUDE_ONLY);
        $widget->setAssoc(array(
            $association,
        ));

        $this->assertTrue($voter->isVisible($widget));
    }

    /**
     * @dataProvider matchingPathsProvider
     */
    public function testExcludeOnly($association)
    {
        $voter = new StringPathVoter('/foo/bar');
        $widget = $this->createWidget();
        $widget->setVisibility(Widget::EXCLUDE_ONLY);
        $widget->setAssoc(array(
            $association,
        ));

        $this->assertFalse($voter->isVisible($widget));
    }

    public function matchingPathsProvider()
    {
        return array(
            array('/foo/bar/'),
            array('/foo/bar'),
            array('/foo/*/'),
            array('/foo/*'),
            array('/*/'),
            array('/*'),
            array('*'),
        );
    }
}