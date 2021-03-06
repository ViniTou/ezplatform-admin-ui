<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformAdminUi\Behat\PageElement\Fields;

use Behat\Mink\Element\NodeElement;
use EzSystems\Behat\Browser\Context\BrowserContext;
use PHPUnit\Framework\Assert;

class Keywords extends EzFieldElement
{
    /** @var string Name by which Element is recognised */
    public const ELEMENT_NAME = 'Keywords';
    private $setKeywordsValueScript = 'document.getElementById(\'%s\').value = \'%s\';';

    public function __construct(BrowserContext $context, string $locator, string $label)
    {
        parent::__construct($context, $locator, $label);
        $this->fields['fieldInput'] = 'input';
        $this->fields['keywordItem'] = '.ez-keyword__item';
    }

    public function setValue(array $parameters): void
    {
        $fieldInput = $this->context->findElement(
            sprintf('%s %s', $this->fields['fieldContainer'], $this->fields['fieldInput'])
        );

        Assert::assertNotNull($fieldInput, sprintf('Input for field %s not found.', $this->label));

        $keywordInputId = $fieldInput->getAttribute('id');
        $fieldInput->focus();
        $this->context->getSession()->getDriver()->executeScript(sprintf($this->setKeywordsValueScript, $keywordInputId, ''));
        $this->context->getSession()->getDriver()->executeScript(sprintf($this->setKeywordsValueScript, $keywordInputId, $parameters['value']));
        $fieldInput->blur();
    }

    public function getValue(): array
    {
        $fieldInput = $this->context->findElement(
            sprintf('%s %s', $this->fields['fieldContainer'], $this->fields['fieldInput'])
        );

        Assert::assertNotNull($fieldInput, sprintf('Input for field %s not found.', $this->label));

        return [$fieldInput->getValue()];
    }

    public function verifyValueInItemView(array $values): void
    {
        $expectedValues = $this->parseValueString($values['value']);

        $actualValues = array_map(function (NodeElement $element) {
            return $element->getText();
        }, $this->context->findAllElements(sprintf('%s %s', $this->fields['fieldContainer'], $this->fields['keywordItem'])));
        sort($actualValues);

        Assert::assertEquals($expectedValues, $actualValues);
    }

    private function parseValueString(string $value): array
    {
        $parsedValues = [];

        foreach (explode(',', $value) as $singleValue) {
            $parsedValues[] = trim($singleValue);
        }

        sort($parsedValues);

        return $parsedValues;
    }
}
