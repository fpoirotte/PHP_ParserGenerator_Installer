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
            ScriptEvents::POST_INSTALL_CMD => 'onInstallOrUpdate',
            ScriptEvents::POST_UPDATE_CMD => 'onInstallOrUpdate',
        );
    }

    public function onInstallOrUpdate()
    {
        // Install/remove parsers as appropriate.
        // We do this in one go rather than on the fly for each package
        // installation/update/removal to get a neater (grouped) output.
        foreach ($this->installer->getOperations() as $operation) {
            list($op, $pkg) = $operation;
            switch ($op) {
                case '-':
                    $this->installer->removeParsers($pkg);
                    break;

                case '+':
                    $this->installer->installParsers($pkg, true);
                    break;

                default:
                    throw new \RuntimeException('Unknown operation');
            }
        }

        // Don't forget to handle the root package's parsers,
        // if any has been defined. Note: parsers for the root
        // package will never be removed automatically.
        $rootPackage = $this->composer->getPackage();
        if ($this->installer->supports($rootPackage->getType())) {
            $this->installer->installParsers($rootPackage, false);
        }
    }
}
