<?php

namespace fpoirotte\PHP_ParserGenerator_Installer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;
use Composer\Repository\InstalledRepositoryInterface;

class Installer extends LibraryInstaller
{
    const SUPPORTED_TYPE = 'php-parsers';

    private static function normalizeParsers(PackageInterface $package)
    {
        $res = array();
        $extra = $package->getExtra();
        if (isset($extra['php-parsers']) && is_array($extra['php-parsers'])) {
            foreach ($extra['php-parsers'] as $target => $source) {
                $res[is_string($target) ? $target : $source] = $source;
            }
        }
        return $res;
    }

    private static function getOutputPrefix($outfile)
    {
        $a = pathinfo($outfile);
        if (isset($a['extension'])) {
            $ext = '.' . $a['extension'];
            $outfile = (string) substr(
                $outfile,
                0,
                strlen($outfile) - strlen($ext)
            );
        }
        return $outfile;
    }

    public function installParsers(PackageInterface $package)
    {
        $phplemon = getenv('PHPLEMON');
        if ($phplemon === false || $phplemon === '') {
            $phplemon = dirname(dirname(dirname(__DIR__))) .
                        DIRECTORY_SEPARATOR . 'bin' .
                        DIRECTORY_SEPARATOR . 'phplemon';
        }

        if (!file_exists($phplemon)) {
            throw new \RuntimeException('phplemon not found');
        }

        $phplemon = escapeshellcmd($phplemon);
        $parsers = self::normalizeParsers($package);
        foreach ($parsers as $target => $source) {
            $outfile = self::getOutputPrefix($target) . '.php';
            if ((int) @filemtime($outfile) > (int) @filemtime($source)) {
                continue;
            }

            $this->io->writeError("<info>Compiling '$target' from '$source'</info>");
            $target = escapeshellarg('o=' . $target);
            $source = escapeshellarg($source);
            passthru("$phplemon -q -s $target $source");
        }
    }

    private function removeParsers(PackageInterface $package)
    {
        $parsers = self::normalizeParsers($package);
        foreach ($parsers as $target => $source) {
            $target = self::getOutputPrefix($target);
            @unlink($target . '.php');
            @unlink($target . '.out');
        }
    }

    /**
     * Installs specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $res = parent::install($repo, $package);
        $this->installParsers($package);
        return $res;
    }

    /**
     * Updates specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $initial already installed package version
     * @param PackageInterface             $target  updated version
     *
     * @throws InvalidArgumentException if $initial package is not installed
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $res = parent::update($repo, $initial, $target);
        $this->removeParsers($initial);
        $this->installParsers($target);
        return $res;
    }

    /**
     * Uninstalls specific package.
     *
     * @param InstalledRepositoryInterface $repo    repository in which to check
     * @param PackageInterface             $package package instance
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $res = parent::uninstall($repo, $package);
        $this->removeParsers($package);
        return $res;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return self::SUPPORTED_TYPE === $packageType;
    }
}
