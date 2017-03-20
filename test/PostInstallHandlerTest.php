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
use ErgonTech\ModuleGenerator\PostInstallHandler;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Mpw\MageScaffold\ModuleScaffolder;


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

    /**
     * @var ModuleScaffolder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleScaffolder;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

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
                'askAndValidate',
                'askConfirmation'
            ])
            ->getMock();

        $this->event->expects(static::any())
            ->method('getIo')
            ->willReturn($this->io);

    }
    protected function mocksModuleScaffolder()
    {
        $this->moduleScaffolder = $this->getMockBuilder(ModuleScaffolder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'generate'
            ])
            ->getMock();

        PostInstallHandler::setModuleScaffolder($this->moduleScaffolder);
    }

    protected function mocksFilesystem()
    {
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        PostInstallHandler::setFilesystem($this->filesystem);
    }

    public function testModuleNameValidation()
    {
        static::setExpectedException(\Exception::class);
        PostInstallHandler::validateModuleName('asdf');
        static::assertEquals('Asdf_Asdf', PostInstallHandler::validateModuleName('Asdf_Asdf'));
    }

    public function testVersionStringValidation()
    {
        static::setExpectedException(\Exception::class);
        PostInstallHandler::validateModuleVersion('asdf');
        static::assertEquals('0.1.0', PostInstallHandler::validateModuleVersion('0.1.0'));
        static::assertEquals('1.0', PostInstallHandler::validateModuleVersion('1.0'));
        static::assertEquals('1', PostInstallHandler::validateModuleVersion('1'));
    }

    public function testFilesystemMutation()
    {
        static::assertInstanceOf(Filesystem::class, PostInstallHandler::getFilesystem());

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        PostInstallHandler::setFilesystem($filesystem);

        static::assertSame($filesystem, PostInstallHandler::getFilesystem());
    }

    public function testModuleScaffolderMutation()
    {
        $mockModuleScaffolder = $this->getMockBuilder(ModuleScaffolder::class)
            ->disableOriginalConstructor()
            ->getMock();

        static::assertInstanceOf(ModuleScaffolder::class, PostInstallHandler::getModuleScaffolder());

        PostInstallHandler::setModuleScaffolder($mockModuleScaffolder);
        static::assertSame($mockModuleScaffolder, PostInstallHandler::getModuleScaffolder());
    }

    public function testValidInput()
    {
        $this->mocksFilesystem();
        $this->mocksModuleScaffolder();

        $this->io->expects(static::any())
            ->method('askAndValidate')
            ->withConsecutive(['Enter Module Name: ', static::isType('callable')], ['Enter Module Version: [<comment>0.1.0</comment>] ', static::isType('callable'), null, '0.1.0'])
            ->willReturnCallback(function ($prompt, $cb, $attempts, $default) {
                if (($prompt === 'Enter Module Name: ') && is_callable($cb)) {
                    return 'Asdf_Asdf';
                }

                if (($prompt === 'Enter Module Version: [<comment>0.1.0</comment>] ') && is_callable($cb)) {
                    return '0.1.0';
                }

                return false;
            });

        $this->io->expects(static::once())
            ->method('askConfirmation')
            ->with('Community Code Pool [<comment>yes</comment>]? ', true)
            ->willReturn(true);

        $this->moduleScaffolder->expects(static::once())
            ->method('generate')
            ->with(
                'Asdf_Asdf',
                true,
                true,
                '0.1.0');

        PostInstallHandler::promptForModuleInformation($this->event);
    }
}
