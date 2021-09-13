<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\helpers;

use Craft;

use craft\helpers\ArrayHelper;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Tag\Summery;

use yii\base\InvalidConfigException;
use yii\di\ServiceLocator;

class Autocomplete
{
    const COMPLETION_KEY = '__completions';

    /**
     * Faux enum, from: https://microsoft.github.io/monaco-editor/api/enums/monaco.languages.completionitemkind.html
     */
    const CompletionItemKind = [
        'Class' => 5,
        'Color' => 19,
        'Constant' => 14,
        'Constructor' => 2,
        'Customcolor' => 22,
        'Enum' => 15,
        'EnumMember' => 16,
        'Event' => 10,
        'Field' => 3,
        'File' => 20,
        'Folder' => 23,
        'Function' => 1,
        'Interface' => 7,
        'Issue' => 26,
        'Keyword' => 17,
        'Method' => 0,
        'Module' => 8,
        'Operator' => 11,
        'Property' => 9,
        'Reference' => 21,
        'Snippet' => 27,
        'Struct' => 6,
        'Text' => 18,
        'TypeParameter' => 24,
        'Unit' => 12,
        'User' => 25,
        'Value' => 13,
        'Variable' => 4,
    ];

    /**
     * Core function that generates the autocomplete array
     */
    public static function generate()
    {
        $completionList = [];
        // Iterate through the globals in the Twig context
        /* @noinspection PhpInternalEntityUsedInspection */
        $globals = Craft::$app->view->getTwig()->getGlobals();
        foreach ($globals as $key => $value) {
            $type = gettype($value);
            switch ($type) {
                case 'object':
                    self::parseObject($completionList, $key, $value, '');
                    break;

                case 'array':
                case 'boolean':
                case 'double':
                case 'integer':
                case 'string':
                    $kind = self::CompletionItemKind['Variable'];
                    $path = $key;
                    $normalizedKey = preg_replace("/[^A-Za-z]/", '', $key);
                    if (ctype_upper($normalizedKey)) {
                        $kind = self::CompletionItemKind['Constant'];
                    }
                    ArrayHelper::setValue($completionList, $path, [
                        self::COMPLETION_KEY => [
                            'detail' => "{$type}: {$value}",
                            'kind' => $kind,
                            'label' => $key,
                            'insertText' => $key,
                        ]
                    ]);
                    break;
            }
        }


        return $completionList;
    }

    public static function parseObject(array &$completionList, string $name, $object, string $path = '')
    {
        // Create the docblock parser
        $customTags = [
            new Summery(),
        ];
        $tags = PhpDocumentor::tags()->with($customTags);
        $parser = new PhpdocParser($tags);
        $path = trim(implode('.', [$path, $name]), '.');
        // The class itself
        self::getClassCompletion($completionList, $object, $parser, $name, $path);
        // ServiceLocator Components
        self::getComponentCompletion($completionList, $object, $path);
        // Class properties
        self::getPropertyCompletion($completionList, $object, $parser, $path);
        // Class methods
        self::getMethodCompletion($completionList, $object, $parser, $path);
    }

    /**
     * @param array $completionList
     * @param $object
     * @param PhpdocParser $parser
     * @param string $name
     * @param $path
     */
    protected static function getClassCompletion(array &$completionList, $object, PhpdocParser $parser, string $name, $path)
    {
        try {
            $reflectionClass = new \ReflectionClass($object);
        } catch (\ReflectionException $e) {
            return;
        }
        // Information on the class itself
        $className = $reflectionClass->getName();
        $type = 'Class';
        $docs = $reflectionClass->getDocComment();
        try {
            $annotations = $parser->parse($docs);
        } catch (\Throwable $e) {
            // That's okay
        }
        ArrayHelper::setValue($completionList, $path, [
            self::COMPLETION_KEY => [
                'detail' => "{$type}: {$className}",
                'documentation' => $annotations['description'] ?? $docs,
                'kind' => self::CompletionItemKind['Class'],
                'label' => $name,
                'insertText' => $name,
            ]
        ]);
    }

    /**
     * @param array $completionList
     * @param $object
     * @param $path
     */
    protected static function getComponentCompletion(array &$completionList, $object, $path)
    {
        if ($object instanceof ServiceLocator) {
            foreach ($object->getComponents() as $key => $value) {
                $componentObject = null;
                try {
                    $componentObject = $object->get($key);
                } catch (InvalidConfigException $e) {
                }
                if ($componentObject) {
                    self::parseObject($completionList, $key, $componentObject, $path);
                }
            }
        }
    }

    /**
     * @param array $completionList
     * @param $object
     * @param PhpdocParser $parser
     * @param string $path
     */
    protected static function getPropertyCompletion(array &$completionList, $object, PhpdocParser $parser, string $path)
    {
        try {
            $reflectionClass = new \ReflectionClass($object);
        } catch (\ReflectionException $e) {
            return;
        }
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->isPublic()) {
                $type = "Property";
                $docs = $reflectionProperty->getDocComment();
                try {
                    $annotations = $parser->parse($docs);
                } catch (\Throwable $e) {
                    // That's okay
                }
                // Figure out the type
                $detail = $annotations['var']['type'] ?? "Property";
                if ($detail === "Property") {
                    if (preg_match('/@var\s+([^\s]+)/', $docs, $matches)) {
                        list(, $type) = $matches;
                        $detail = $type;
                    } else {
                        $detail = "Property";
                    }
                }
                if ($detail === "Property") {
                    if ((PHP_MAJOR_VERSION >= 7 && PHP_MINOR_VERSION >= 4) || (PHP_MAJOR_VERSION >= 8)) {
                        if ($reflectionProperty->hasType()) {
                            $reflectionType = $reflectionProperty->getType();
                            if ($reflectionType && $reflectionType instanceof \ReflectionNamedType) {
                                $type = $reflectionType::getName();
                                $detail = $type;
                            }
                        }
                        if (PHP_MAJOR_VERSION >= 8) {
                            if ($reflectionProperty->hasDefaultValue()) {
                                $value = $reflectionProperty->getDefaultValue();
                                if (is_array($value)) {
                                    $value = json_encode($value);
                                }
                                if (!empty($value)) {
                                    $detail = "{$type}: {$value}";
                                }
                            }
                        }
                    }
                }
                $propertyName = $reflectionProperty->getName();
                $thisPath = trim(implode('.', [$path, $propertyName]), '.');
                $label = $propertyName;
                $varDescription = $annotations['var']['description'] ?? null;
                if ($varDescription) {
                    $varDescription = str_replace(['*/', ' * '], '', $varDescription);
                }
                ArrayHelper::setValue($completionList, $thisPath, [
                    self::COMPLETION_KEY => [
                        'detail' => $detail,
                        'documentation' => $varDescription ?? $annotations['description'] ?? $docs,
                        'kind' => self::CompletionItemKind['Property'],
                        'label' => $label,
                        'insertText' => $label,
                        'sortText' => '_' . $label,
                    ]
                ]);
                // Recurse through if this is an object
                if (isset($object->$propertyName) && is_object($object->$propertyName)) {
                    Craft::info('MOOF - ' . $propertyName . ' - ' . $detail, __METHOD__);
                    Craft::info('WOOF - ' . $path, __METHOD__);
                    if ($propertyName === 'app') {
                        self::parseObject($completionList, $propertyName, $object->$propertyName, $path);
                    }
                }
            }
        }
    }

    /**
     * @param array $completionList
     * @param $object
     * @param PhpdocParser $parser
     * @param string $path
     */
    protected static function getMethodCompletion(array &$completionList, $object, PhpdocParser $parser, string $path)
    {
        try {
            $reflectionClass = new \ReflectionClass($object);
        } catch (\ReflectionException $e) {
            return;
        }
        $reflectionMethods = $reflectionClass->getMethods();
        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            if ($methodName[0] !== '_' && $reflectionMethod->isPublic()) {
                $type = "Method";
                $detail = $type;
                $docs = $reflectionMethod->getDocComment();
                try {
                    $annotations = $parser->parse($docs);
                } catch (\Throwable $e) {
                    // That's okay
                }
                $thisPath = trim(implode('.', [$path, $methodName]), '.');
                $label = $methodName . '()';
                $varDescription = $annotations['var']['description'] ?? null;
                if ($varDescription) {
                    $varDescription = str_replace(['*/', ' * '], '', $varDescription);
                }
                ArrayHelper::setValue($completionList, $thisPath, [
                    self::COMPLETION_KEY => [
                        'detail' => $detail,
                        'documentation' => $varDescription ?? $annotations['description'] ?? $docs,
                        'kind' => self::CompletionItemKind['Method'],
                        'label' => $label,
                        'insertText' => $label,
                        'sortText' => '__' . $label,
                    ]
                ]);
            }
        }
    }
}
