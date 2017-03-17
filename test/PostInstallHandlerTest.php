<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthewwells
 * Date: 3/16/17
 * Time: 17:50
 */

namespace test\ErgonTech\ModuleGenerator;


use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Script\Event;
use ErgonTech\ModuleGenerator\CodePoolChoiceException;
use ErgonTech\ModuleGenerator\ModuleNameException;
use ErgonTech\ModuleGenerator\PostInstallHandler;


class PostInstallHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Event|\PHPUnit_Framework_MockObject_MockObject
     */
    private $event;

    /**
     * @var IOInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $io;

    protected function setUp()
    {
        parent::setUp();
        $this->event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIo'
            ])
            ->getMock();

        $this->io = $this->getMockBuilder(NullIO::class)
            ->setMethods([
                'ask', 'select'
            ])
            ->getMock();

        $this->event->expects(static::any())
            ->method('getIo')
            ->willReturn($this->io);
    }

    public function testInvalidModuleName()
    {
        $this->io->expects(static::once())
            ->method('ask')
            ->with('Enter Module name')
            ->willReturn('asdf'); // <-- that's not a valid module name!

        $subject = new PostInstallHandler();

        $this->setExpectedException(ModuleNameException::class);
        $subject->promptForModuleInformation($this->event);
    }

    public function testInvalidCodePool()
    {
        $this->io->expects(static::once())
            ->method('ask')
            ->with('Enter Module name')
            ->willReturn('Asdf_Asdf');

        $this->io->expects(static::once())
            ->method('select')
            ->with('Choose Code Pool')
            ->willReturn('blah');


        $subject = new PostInstallHandler();
        $this->setExpectedException(CodePoolChoiceException::class);
        $subject->promptForModuleInformation($this->event);
    }

    public function testValidInput()
    {
        $this->io->expects(static::any())
            ->method('ask')
            ->with('Enter Module name')
            ->willReturn('Asdf_Asdf');

        $this->io->expects(static::once())
            ->method('select')
            ->with('Choose Code Pool')
            ->willReturn('community');

        $subject = new PostInstallHandler();
        $subject->promptForModuleInformation($this->event);
    }
}
