<?php

namespace Ulabox\PhpSpec\Extension\BusySpec\Generator;

use PhpSpec\CodeGenerator\Generator\GeneratorInterface;
use PhpSpec\Console\IO;
use PhpSpec\Locator\ResourceInterface;
use PhpSpec\Util\Filesystem;

abstract class AbstractGenerator implements GeneratorInterface
{
    /**
     * @var \PhpSpec\Console\IO
     */
    private $io;
    /**
     * @var \PhpSpec\Util\Filesystem
     */
    private $filesystem;
    /**
     * @param IO               $io
     * @param Filesystem       $filesystem
     */
    public function __construct(IO $io, Filesystem $filesystem = null)
    {
        $this->io           = $io;
        $this->filesystem   = $filesystem ?: new Filesystem();
    }

    /**
     * @param ResourceInterface $resource
     * @param string $generation
     * @param array $data
     *
     * @return bool
     */
    public function supports(ResourceInterface $resource, $generation, array $data)
    {
        return $this->getCommandTag() === $generation;
    }

    private function createDir($dir)
    {
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    abstract protected function getCommandTag();

    abstract protected function generateCodeForResource(ResourceInterface $resource, array $data);

    abstract protected function getPromptMessage(ResourceInterface $resource, $filepath);

    protected function getSavePath(ResourceInterface $resource)
    {
        return $resource->getSrcFilename();
    }

    /**
     * @param ResourceInterface $resource
     * @param array $data
     */
    public function generate(ResourceInterface $resource, array $data)
    {
        $destination = $this->getSavePath($resource);

        if (
            file_exists($destination) &&
            !$this->io->askConfirmation(sprintf('File "%s" already exists. Overwrite?', basename($destination)), false)
        ) {
            return;
        }

        $directory = dirname($destination);
        if (!file_exists($directory)) {
            $this->createDir($directory);
        }

        $code = $this->generateCodeForResource($resource, $data);

        $this->filesystem->putFileContents($destination, $code);
        $this->io->writeln($this->getPromptMessage($resource, $resource->getSrcFilename()));
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 0;
    }
}
