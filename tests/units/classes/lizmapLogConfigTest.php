<?php

class lizmapLogConfigTest extends PHPUnit_Framework_TestCase
{
    public function getTestModifyData()
    {
        $data1 = array(
            'general' => array(
                'active' => true,
                'profile' => 'lizlog'
            )
        );
        $data2 = array(
            'general' => array(
                'active' => true,
                'profile' => 'lizlog',
                'notExistingProp' => 'test'
            )
        );
        $data3 = array();
        $data4 = array('general' => array(
            'notExistingProp' => true
            )
        );
        return array(
            array($data1, $data1['general'], true),
            array($data1, null, false),
            array($data3, $data1['general'], true),
            array($data1, $data2['general'], true),
            array($data3, $data4, false),
            array($data4, $data3, false),
        );
    }

    /**
     * @dataProvider getTestModifyData
     */

    public function testModify($data, $newData, $expectedReturnValue)
    {
        $testLizmapLogConfig = new lizmapLogConfig($data);
        $this->assertEquals($expectedReturnValue, $testLizmapLogConfig->modify($newData));
        unset($testLizmapLogConfig);
    }

    public function getTestSaveData()
    {
        $data = array(
            'general' => array(
                'active' => true,
                'profile' => 'lizlog'
            )
        );
        $expectedData = array(
            'general' => array(
                'active' => false,
                'profile' => 'lizlog'
            )
        );
        $expectedData2 = array(
            'general' => array(
                'profile' => 'lizlog'            )
        );
        return array(
            array($data, $data, null, null, false),
            array($data, $expectedData, 'active', false, false),
            array($data, $expectedData2, 'active', '', false),
            array($data, $expectedData2, 'active', null, false)
        );
    }

    /**
     * @dataProvider getTestSaveData
     */

    public function testSave($data, $expectedData, $changedProp, $changedValue, $expectedReturnValue)
    {
        $iniFile = __DIR__.'/../tmp/logConfig.ini.php';
        file_put_contents($iniFile, '');

        $ini = new jIniFileModifier($iniFile);
        $testLizmapLogConfig = new lizmapLogConfig($data);
        if ($changedProp) {
            $data['general'][$changedProp] = $changedValue;
        }
        $testLizmapLogConfig->modify($data['general']);
        $this->assertEquals($expectedReturnValue, $testLizmapLogConfig->save($ini));
        $this->assertEquals($expectedData['general'], $ini->getValues('general'));
        unset($ini);
        unset($testLizmapLogConfig);
        unlink($iniFile);
    }

    public function getTestGetLogItemListData()
    {
        $data1 = array(
            'general' => array(),
            'item:test' => array(),
            'item:test2' => array()
        );
        $data2 = array(
            'item:test' => array(),
            'item:test2' => array()
        );
        $data3 = array();
        $data4 = array(
            'not' => array(),
            'valid' => array(),
        );
        $data5 = array(
            'general' => '',
            'item:test' => '',
            'otherSection' => '',
            'item:test2' => ''
        );
        return array(
            array($data1, array('test', 'test2')),
            array($data2, array('test', 'test2')),
            array($data3, array()),
            array($data4, array()),
            array($data5, array('test', 'test2'))
        );
    }

    /**
     * @dataProvider getTestGetLogItemListData
     */

    public function testGetLogItemList($data, $expectedList)
    {
        $testLizmapLogConfig = new lizmapLogConfig($data);
        $list = $testLizmapLogConfig->getLogItemList();
        $this->assertEquals($expectedList, $list);
        unset($testLizmapLogConfig);
    }

    public function getTestGetLogItemData()
    {
        $data = array(
            'general' => array(),
            'item:test' => array(
                'label' => 'label',
                'logCounter' => 'on'
            ),
            'item:test2' => array(
                'label' => 'label2',
                'logCounter' => 'on'
            ),
            'item:test3' => array(
                'label' => 'label3',
                'logCounter' => 'on'
            )
        );
        return array(
            array($data, 'test', true),
            array($data, 'test2', true),
            array($data, 'test3', true),
            array($data, 'testNotExisting', false)
        );
    }

    /**
     * @dataProvider getTestGetLogItemData
     */

    public function testGetLogItem($data, $key, $valid)
    {
        $testLizmapLogConfig = new lizmapLogConfig($data);
        $item = $testLizmapLogConfig->getLogItem($key);
        if (!$valid) {
            $this->assertEquals(null, $item);
            return ;
        }
        $expectedItem = new lizmapLogItem($key, $data['item:'.$key]);
        $item2 = $testLizmapLogConfig->getLogItem($key);
        $this->assertEquals($expectedItem, $item);
        $this->assertSame($item, $item2);
        unset($testLizmapLogConfig, $item, $expectedItem);
    }
}
