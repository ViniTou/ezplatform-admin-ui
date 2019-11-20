<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Form\Type\ChoiceList\Loader;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LanguageResolver;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use EzSystems\EzPlatformAdminUi\UI\Service\VersionStatus;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class VersionInfoChoiceLoader implements ChoiceLoaderInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \EzSystems\EzPlatformAdminUi\UI\Service\VersionStatus */
    private $versionStatus;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentInfo */
    private $contentInfo;

    /** @var \eZ\Publish\API\Repository\LanguageService */
    private $languageService;

    public function __construct(
        ContentService $contentService,
        VersionStatus $versionStatus,
        TranslatorInterface $translator,
        LanguageService $languageService,
        ContentInfo $contentInfo
    ) {
        $this->contentService = $contentService;
        $this->versionStatus = $versionStatus;
        $this->translator = $translator;
        $this->contentInfo = $contentInfo;
        $this->languageService = $languageService;
    }

    public function getChoiceList(): array
    {
        $choices = [];
        $versions = $this->contentService->loadVersions($this->contentInfo);

        foreach ($versions as $version) {
            foreach ($version->languageCodes as $versionLanguageCode) {
                $label = $this->buildSelectLabel(
                    $version,
                    $this->languageService->loadLanguage($versionLanguageCode)
                );

                $choices[$label] = $version->versionNo . '/' . $versionLanguageCode;
            }
        }

        return $choices;
    }

    protected function buildSelectLabel(VersionInfo $versionInfo, Language $language): string
    {
        return $this->translator->trans(
            /** @Desc("%status% - Version %version% - %language%") */
            'version_info.comparison.select_name',
            [
                '%status%' => $this->versionStatus->translateStatus($versionInfo->status),
                '%version%' => $versionInfo->versionNo,
                '%language%' => $language->name,
            ],
            'version'
        );
    }

    public function loadChoiceList($value = null)
    {
        $choices = $this->getChoiceList();

        return new ArrayChoiceList($choices, $value);
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        // Optimize
        $values = array_filter($values);
        if (empty($values)) {
            return [];
        }

        // If no callable is set, values are the same as choices
        if (null === $value) {
            return $values;
        }

        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Optimize
        $choices = array_filter($choices);
        if (empty($choices)) {
            return [];
        }

        // If no callable is set, choices are the same as values
        if (null === $value) {
            return $choices;
        }

        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
