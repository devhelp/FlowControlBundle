<?php

namespace Devhelp\FlowControlBundle\Tests\FlowConfiguration\Reader;

use Devhelp\FlowControlBundle\FlowConfiguration\Reader\AnnotationConfigurationReader;

class AnnotationConfigurationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readReturnFlowStepsThatAreDefinedForGivenMethodUsingAnnotations()
    {
        $expected = array(
            'test-name1' => 'test-step1',
            'test-name2' => 'test-step2',
            'test-name3' => 'test-step3'
        );

        $annotations = array(
            $this->getFlowMock(1),
            $this->getFlowMock(2),
            $this->getFlowMock(3),
        );

        $annotationReader = $this->getAnnotationReaderMock($annotations);

        $reader = new AnnotationConfigurationReader($annotationReader);

        $controller = array(
            '\Devhelp\FlowControlBundle\Tests\Stub\ControllerStub',
            'actionStub'
        );

        $actual = $reader->read($controller);

        $this->assertEquals($expected, $actual['flow_steps']);
    }

    /**
     * @test
     */
    public function readReturnsAutocommitFlagSetToFalseIfDisableAutocommmitAnnotationExists()
    {
        $annotations = array($this->getDisableAutocommitMock());

        $annotationReader = $this->getAnnotationReaderMock($annotations);

        $reader = new AnnotationConfigurationReader($annotationReader);

        $controller = array(
            '\Devhelp\FlowControlBundle\Tests\Stub\ControllerStub',
            'actionStub'
        );

        $configuration = $reader->read($controller);

        $this->assertFalse($configuration['autocommit']);
    }

    /**
     * @test
     */
    public function readReturnsAutocommitFlagSetToTrueIfDisableAutocommmitAnnotationDoesNotExist()
    {
        $annotationReader = $this->getAnnotationReaderMock();

        $reader = new AnnotationConfigurationReader($annotationReader);

        $controller = array(
            '\Devhelp\FlowControlBundle\Tests\Stub\ControllerStub',
            'actionStub'
        );

        $configuration = $reader->read($controller);

        $this->assertTrue($configuration['autocommit']);
    }

    /**
     * @test
     */
    public function readerSupportsArrayAsConfigurationSourceDefinition()
    {
        $reader = new AnnotationConfigurationReader($this->getAnnotationReaderMock());

        $this->assertTrue($reader->supports(array('controller', 'action')));
    }

    private function getAnnotationReaderMock($annotations = array())
    {
        $mock = $this->getMockForAbstractClass('Doctrine\Common\Annotations\Reader');

        $mock
            ->expects($this->any())
            ->method('getMethodAnnotations')
            ->will($this->returnValue($annotations));

        return $mock;
    }

    private function getFlowMock($id)
    {
        $mock = $this
                    ->getMockBuilder('Devhelp\FlowControlBundle\FlowConfiguration\Annotation\Flow')
                    ->disableOriginalConstructor()
                    ->getMock();

        $mock
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('test-name' . $id));

        $mock
            ->expects($this->any())
            ->method('getStep')
            ->will($this->returnValue('test-step' . $id));

        return $mock;
    }

    private function getDisableAutocommitMock()
    {
        $mock = $this
            ->getMockBuilder('Devhelp\FlowControlBundle\FlowConfiguration\Annotation\DisableAutocommit')
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
