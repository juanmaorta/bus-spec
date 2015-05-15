<?php

namespace Ulabox\PhpSpec\Extension\BusySpec\Generator;

use Memio\Memio\Config\Build;
use Memio\Model\Argument;
use Memio\Model\File;
use Memio\Model\FullyQualifiedName;
use Memio\Model\Method;
use Memio\Model\Object;
use PhpSpec\Locator\ResourceInterface;

final class CommandSpecGenerator extends AbstractGenerator
{
    const COMMAND_TAG = 'busify_command_spec';

    protected function getCommandTag()
    {
        return self::COMMAND_TAG;
    }

    protected function generateCodeForResource(ResourceInterface $resource, array $data)
    {
        /** @var Object $structure */
        $structure = Object::make($resource->getSpecClassname());
        $structure->extend(Object::make('PhpSpec\ObjectBehavior'));

        $methodBody = <<<BODY
        \$this->shouldHaveType('{$resource->getSrcClassname()}');
BODY;

        $structure->addMethod(Method::make('it_is_initializable')->setBody($methodBody));

        foreach ($data['params'] as $param) {
            $specExample = sprintf('it_should_retrieve_%s_getter_value', $param);
            $specMethodBody = "";
            $specMethodBody .= "        throw new PendingException('pending implementation');" ."\n";
            $specMethodBody .= "        \$expectation = 'put value here';" ."\n";
            $specMethodBody .= "        \$this->{$param}()->shouldBeLike(\$expectation);";
            $specExampleMethod = Method::make($specExample)->setBody($specMethodBody);
            $structure->addMethod($specExampleMethod);
        }

        $file = File::make($resource->getSpecFilename());
        $file->addFullyQualifiedName(new FullyQualifiedName('PhpSpec\ObjectBehavior'));
        $file->addFullyQualifiedName(new FullyQualifiedName('Prophecy\Argument'));
        $file->addFullyQualifiedName(new FullyQualifiedName('PhpSpec\Exception\Example\PendingException'));
        $file->setStructure($structure);

        $prettyPrinter = Build::prettyPrinter();
        return $prettyPrinter->generateCode($file);
    }

    protected function getPromptMessage(ResourceInterface $resource, $filepath)
    {
        return sprintf(
            "<info>Specification for <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }

    protected function getSavePath(ResourceInterface $resource)
    {
        return $resource->getSpecFilename();
    }
}
