<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Builder;

/**
 * @author Alexandre Daubois <alex.daubois@gmail.com>
 *
 * @internal
 */
class FunctionBuilder
{
    private string $body;
    private array $paramPhpDoc = [];
    private array $use = [];
    private array $require = [];
    private array $param = [];
    private array $paramDefaultValue = [];
    private array $paramAttributes = [];
    private ?string $returnType = null;

    public function __construct(
        private string $name,
        private string $namespace,
    ) {
    }

    public function getDirectory(): string
    {
        return str_replace('\\', \DIRECTORY_SEPARATOR, $this->namespace);
    }

    public function getFilename(): string
    {
        return $this->name.'.php';
    }

    public function build(): string
    {
        $rootPath = explode(\DIRECTORY_SEPARATOR, $this->getDirectory());
        $require = '';
        foreach ($this->require as $class) {
            // figure out relative path.
            $path = explode(\DIRECTORY_SEPARATOR, $class->getDirectory());
            $path[] = $class->getFilename();
            foreach ($rootPath as $key => $value) {
                if ($path[$key] !== $value) {
                    break;
                }
                unset($path[$key]);
            }
            $require .= \sprintf('require_once __DIR__.\DIRECTORY_SEPARATOR.\'%s\';', implode('\'.\DIRECTORY_SEPARATOR.\'', $path))."\n";
        }
        $use = $require ? "\n" : '';
        foreach (array_keys($this->use) as $statement) {
            $use .= \sprintf('use %s;', $statement)."\n";
        }

        $params = [];
        foreach ($this->param as $name => $param) {
            $params[$name] = (isset($this->paramAttributes[$name]) ? $this->paramAttributes[$name].' ' : '').$param.' $'.$name;

            if (isset($this->paramDefaultValue[$name])) {
                $params[$name] .= ' = '.$this->paramDefaultValue[$name];
            }
        }

        return strtr('<?php

namespace NAMESPACE;

REQUIREUSE
/**
 * This class is automatically generated to help in creating a config.
 *
PHPDOC
 */
function NAME(ARGUMENTS)RETURNTYPE {
BODY
}
',
            [
                'NAMESPACE' => $this->namespace,
                'REQUIRE' => $require,
                'USE' => $use,
                'NAME' => $this->getName(),
                'BODY' => $this->body,
                'PHPDOC' => implode("\n *\n", array_map(fn ($param, $phpDoc) => ' * @param '.$phpDoc.' $'.$param, array_keys($this->paramPhpDoc), $this->paramPhpDoc)),
                'ARGUMENTS' => implode(', ', $params),
                'RETURNTYPE' => $this->returnType ? ': '.$this->returnType : '',
            ]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function addRequire(self $class): void
    {
        $this->require[] = $class;
    }

    public function addUse(string $class): void
    {
        $this->use[$class] = true;
    }

    public function addParam(string $type, string $name): void
    {
        $this->param[$name] = $type;
    }

    public function addParamAttribute(string $parameterName, string $attribute): void
    {
        $this->paramAttributes[$parameterName] = $attribute;
    }

    public function addParamDefaultValue(string $parameterName, string $defaultValue): void
    {
        $this->paramDefaultValue[$parameterName] = $defaultValue;
    }

    public function addParamPhpDoc(string $parameterName, string $paramPhpDoc): void
    {
        $this->paramPhpDoc[$parameterName] = $paramPhpDoc;
    }

    public function setReturnType(?string $returnType): void
    {
        $this->returnType = $returnType;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}
