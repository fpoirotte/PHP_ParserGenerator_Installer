<?php

namespace fpoirotte\PHP_ParserGenerator_Installer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\ScriptEvents;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private $installer;
    private $composer;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->installer);
    }

    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'onRootPackageChange',
            ScriptEvents::POST_UPDATE_CMD => 'onRootPackageChange',
        );
    }

    public function onRootPackageChange()
    {
        $rootPackage = $this->composer->getPackage();
        if ($this->installer->supports($rootPackage->getType())) {
            $this->installer->installParsers($rootPackage, false);
        }
    }
}
