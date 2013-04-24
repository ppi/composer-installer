<?php

namespace PPIInstaller;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\Script\Event;

/**
 * Component Installer for Composer.
 */
class Installer extends LibraryInstaller
{

    protected $packageType = 'ppi-module';

    /**
     * {@inheritDoc}
     *
     * Components are supported by all packages.
     */
    public function supports($packageType)
    {
        // Components are supported by all package types. We will just act on
        // the root package's scripts if available.
        $rootPackage = isset($this->composer) ? $this->composer->getPackage() : null;
/*
        if (isset($rootPackage)) {
            // Act on the "post-autoload-dump" command so that we can act on all
            // the installed packages.
            $scripts = $rootPackage->getScripts();
            $scripts['post-autoload-dump']['component-installer'] = 'ComponentInstaller\\Installer::postAutoloadDump';
            $rootPackage->setScripts($scripts);
        }
*/
        
//        var_dump('ppi-module installer supports this', (bool) ($packageType === $this->packageType)); exit;
        
        // Explicitly state support of "ppi-module" packages.
        return (bool) ($packageType === $this->packageType);
    }

    /**
     * Script callback; Acted on after the autoloader is dumped.
     */
    public static function postAutoloadDump(Event $event)
    {
        // Retrieve basic information about the environment and present a
        // message to the user.
        $composer = $event->getComposer();
        $io = $event->getIO();
        $io->write('<info>Compiling component files</info>');

        // Set up all the processes.
        $processes = array(
            "ComponentInstaller\\Process\\CopyProcess",
            "ComponentInstaller\\Process\\RequireJsProcess",
            "ComponentInstaller\\Process\\RequireCssProcess",
        );

        // Initialize and execute each process in sequence.
        foreach ($processes as $class) {
            $process = new $class($composer, $io);
            // When an error occurs during initialization, end the process.
            if (!$process->init()) {
                $io->write('<error>An error occurred while initializing the process.</info>');
                break;
            }
            $process->process();
        }
    }
}
