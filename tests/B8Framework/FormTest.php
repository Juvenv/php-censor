<?php

namespace Tests\b8;

use b8\Form, b8\Config;

class FormTest extends \PHPUnit\Framework\TestCase
{
    public function testFormBasics()
    {
        $f = new Form();
        $f->setAction('/');
        $f->setMethod('POST');

        $this->assertTrue($f->getAction() == '/');
        $this->assertTrue($f->getMethod() == 'POST');

        $config = new Config([
            'b8' => [
                'view' => [
                    'path' => __DIR__ . '/data/view/'
                ]
            ]
        ]);

        $this->assertTrue($f->render('form') == '/POST');

        Config::getInstance()->set('b8.view.path', '');
        $this->assertTrue(strpos((string)$f, '<form') !== false);
    }

    public function testElementBasics()
    {
        $f = new Form\Element\Text('element-name');
        $f->setId('element-id');
        $f->setLabel('element-label');
        $f->setClass('element-class');
        $f->setContainerClass('container-class');

        $this->assertTrue($f->getName() == 'element-name');
        $this->assertTrue($f->getId() == 'element-id');
        $this->assertTrue($f->getLabel() == 'element-label');
        $this->assertTrue($f->getClass() == 'element-class');
        $this->assertTrue($f->getContainerClass() == 'container-class');

        $output = $f->render();
        $this->assertTrue(is_string($output));
        $this->assertTrue(!empty($output));
        $this->assertTrue(strpos($output, 'container-class') !== false);
    }

    public function testInputBasics()
    {
        $f = new Form\Element\Text();
        $f->setValue('input-value');
        $f->setRequired(true);
        $f->setValidator(function ($value) {
            return ($value == 'input-value');
        });

        $this->assertTrue($f->getValue() == 'input-value');
        $this->assertTrue($f->getRequired() == true);
        $this->assertTrue(is_callable($f->getValidator()));
    }

    public function testInputValidation()
    {
        $f = new Form\Element\Text();
        $f->setRequired(true);
        $this->assertFalse($f->validate());

        $f->setRequired(false);
        $f->setPattern('input\-value');
        $this->assertFalse($f->validate());

        $f->setValue('input-value');
        $this->assertTrue($f->validate());

        $f->setValidator(function ($item) {
            if ($item != 'input-value') {
                throw new \Exception('Invalid input value.');
            }
        });

        $this->assertTrue($f->validate());

        $f->setValue('fail');
        $f->setPattern(null);
        $this->assertFalse($f->validate());
    }

    public function testFieldSetBasics()
    {
        $f = new Form\FieldSet();
        $f2 = new Form\FieldSet('group');
        $f3 = new Form\FieldSet();

        $t = new Form\Element\Text('one');
        $t->setRequired(true);
        $f2->addField($t);

        $t = new Form\Element\Text('two');
        $f2->addField($t);

        $t = new Form\Element\Text('three');
        $f3->addField($t);

        $f->addField($f2);
        $f->addField($f3);

        $this->assertFalse($f->validate());

        $f->setValues(['group' => ['one' => 'ONE', 'two' => 'TWO'], 'three' => 'THREE']);

        $values = $f->getValues();
        $this->assertTrue(is_array($values));
        $this->assertTrue(array_key_exists('group', $values));
        $this->assertTrue(array_key_exists('one', $values['group']));
        $this->assertTrue(array_key_exists('three', $values));
        $this->assertTrue($values['group']['one'] == 'ONE');
        $this->assertTrue($values['group']['two'] == 'TWO');
        $this->assertTrue($values['three'] == 'THREE');
        $this->assertTrue($f->validate());

        $html = $f->render();
        $this->assertTrue(strpos($html, 'one') !== false);
        $this->assertTrue(strpos($html, 'two') !== false);
    }

    public function testElements()
    {
        $e = new Form\Element\Button();
        $this->assertTrue($e->validate());
        $this->assertTrue(strpos($e->render(), 'button') !== false);

        $e = new Form\Element\Checkbox();
        $e->setCheckedValue('ten');
        $this->assertTrue($e->getCheckedValue() == 'ten');
        $this->assertTrue(strpos($e->render(), 'checkbox') !== false);
        $this->assertTrue(strpos($e->render(), 'checked') === false);

        $e->setValue(true);
        $this->assertTrue(strpos($e->render(), 'checked') !== false);

        $e->setValue('ten');
        $this->assertTrue(strpos($e->render(), 'checked') !== false);

        $e->setValue('fail');
        $this->assertTrue(strpos($e->render(), 'checked') === false);

        $e = new Form\Element\CheckboxGroup();
        $this->assertTrue(strpos($e->render(), 'group') !== false);

        $e = new Form\ControlGroup();
        $this->assertTrue(strpos($e->render(), 'group') !== false);

        $e = new Form\Element\Email();
        $this->assertTrue(strpos($e->render(), 'email') !== false);

        $e = new Form\Element\Select();
        $e->setOptions(['key' => 'Val']);
        $html = $e->render();
        $this->assertTrue(strpos($html, 'select') !== false);
        $this->assertTrue(strpos($html, 'option') !== false);
        $this->assertTrue(strpos($html, 'key') !== false);
        $this->assertTrue(strpos($html, 'Val') !== false);

        $e = new Form\Element\Submit();
        $this->assertTrue($e->validate());
        $this->assertTrue(strpos($e->render(), 'submit') !== false);

        $e = new Form\Element\Text();
        $e->setValue('test');
        $this->assertTrue(strpos($e->render(), 'test') !== false);

        $e = new Form\Element\TextArea();
        $e->setRows(10);
        $this->assertTrue(strpos($e->render(), '10') !== false);

        $e = new Form\Element\Url();
        $this->assertTrue(strpos($e->render(), 'url') !== false);
    }
}