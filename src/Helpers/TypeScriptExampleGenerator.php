<?php

declare(strict_types=1);

namespace Zenith\LaravelPlus\Helpers;

class TypeScriptExampleGenerator
{
    private array $interfaces = [];

    private array $enums = [];

    public function convert(array $data, string $interfaceName): string
    {
        $this->parseObject($data, $interfaceName);

        return implode("\n\n", array_merge($this->interfaces, $this->enums));
    }

    private function parseObject($obj, string $name): string
    {
        if (! is_array($obj) && ! is_object($obj)) {
            return '';
        }

        $fields = [];
        foreach ($obj as $key => $value) {
            $comment = $value['comment'] ?? '';
            $type = $value['type'] ?? '';
            $enumValues = $value['enums'] ?? [];

            switch ($type) {
                case 'int':
                    $tsType = 'number';
                    break;
                case 'string':
                    $tsType = 'string';
                    break;
                case 'enum':
                    $tsType = $name.ucfirst($key);
                    $this->enums[] = $this->generateEnum($tsType, $enumValues);
                    break;
                case 'object_array':
                    $nestedName = ucfirst($key);
                    $tsType = $this->parseObject($value['value'], $nestedName).'[]';
                    break;
                default:
                    if (is_array($value['value']) || is_object($value['value'])) {
                        $nestedName = ucfirst($key);
                        $tsType = $this->parseObject($value['value'], $nestedName);
                    } else {
                        $tsType = 'any';
                    }
            }

            $fields[] = "  /** $comment */\n  $key: $tsType;";
        }

        $interfaceDef = "interface $name {\n".implode("\n", $fields)."\n}";
        $this->interfaces[] = $interfaceDef;

        return $name;
    }

    private function generateEnum(string $name, array $values): string
    {
        $enumFields = [];
        foreach ($values as $key => $value) {
            $enumFields[] = "  /** $value */\n  $key = \"$key\"";
        }

        return "enum $name {\n".implode(",\n", $enumFields)."\n}";
    }
}
