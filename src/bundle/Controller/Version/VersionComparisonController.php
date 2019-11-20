<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUiBundle\Controller\Version;

use eZ\Publish\API\Repository\ContentComparisonService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionChoiceData;
use EzSystems\EzPlatformAdminUi\Form\Data\VersionComparisonData;
use EzSystems\EzPlatformAdminUi\Form\Type\Content\VersionComparisonType;
use EzSystems\EzPlatformAdminUi\Util\FieldDefinitionGroupsUtil;
use EzSystems\EzPlatformAdminUiBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class VersionComparisonController extends Controller
{
    /** @var \eZ\Publish\API\Repository\ContentComparisonService */
    private $compareService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \EzSystems\EzPlatformAdminUi\Util\FieldDefinitionGroupsUtil */
    private $fieldDefinitionGroupsUtil;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    public function __construct(
        ContentComparisonService $contentComparisonService,
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FieldDefinitionGroupsUtil $fieldDefinitionGroupsUtil,
        FormFactoryInterface $formFactory
    ) {
        $this->compareService = $contentComparisonService;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->fieldDefinitionGroupsUtil = $fieldDefinitionGroupsUtil;
        $this->formFactory = $formFactory;
    }

    public function sideBySideCompareAction(
        ContentInfo $contentInfo,
        int $versionNoA,
        string $languageCodeA = null,
        int $versionNoB = null,
        string $languageCodeB = null
    ) {
        $contentA = $this->contentService->loadContent($contentInfo->id, $languageCodeA ? [$languageCodeA] : null, $versionNoA);
        $contentType = $this->contentTypeService->loadContentType($contentA->contentInfo->contentTypeId);
        $contentAfieldDefinitionsByGroup = $this->fieldDefinitionGroupsUtil->groupFieldDefinitions($contentType->getFieldDefinitions());
        $versionInfoA = $this->contentService->loadVersionInfo($contentInfo, $versionNoA);

        $contentB = null;
        $versionInfoB = null;
        if ($versionNoB) {
            $contentB = $this->contentService->loadContent($contentInfo->id, $languageCodeB ? [$languageCodeB] : null, $versionNoB);
            $versionInfoB = $this->contentService->loadVersionInfo($contentInfo, $versionNoB);
        }

        $selectVersionsForm = $this->getForm($contentInfo, $languageCodeA, $languageCodeB, $versionInfoA, $versionInfoB);

        return $this->render(
            '@admin/content/comparison/side_by_side.html.twig',
                [
                    'content_a' => $contentA,
                    'content_b' => $contentB,
                    'field_definitions_by_group' => $contentAfieldDefinitionsByGroup,
                    'select_versions_form' => $selectVersionsForm->createView(),
                ]
        );
    }

    public function compareAction(
        ContentInfo $contentInfo,
        int $versionNoA,
        int $versionNoB,
        string $languageCode = null
    ) {
        $versionInfoA = $this->contentService->loadVersionInfo($contentInfo, $versionNoA);
        $versionInfoB = $this->contentService->loadVersionInfo($contentInfo, $versionNoB);

        $versionDiff = $this->compareService->compareVersions(
            $versionInfoA,
            $versionInfoB,
            $languageCode
        );

        $contentA = $this->contentService->loadContentByVersionInfo($versionInfoA, $languageCode ? [$languageCode] : null);
        $contentAfieldDefinitionsByGroup = $this->fieldDefinitionGroupsUtil->groupFieldDefinitionsDiff($versionDiff);
        $selectVersionsForm = $this->getForm($contentInfo, $languageCode, $languageCode, $versionInfoA, $versionInfoB);

        return $this->render(
            '@admin/content/comparison/single.html.twig',
            [
                'version_diff' => $versionDiff,
                'content_a' => $contentA,
                'field_definitions_by_group' => $contentAfieldDefinitionsByGroup,
                'select_versions_form' => $selectVersionsForm->createView(),
            ]
        );
    }

    protected function getForm(
        ContentInfo $contentInfo,
        string $languageCodeA,
        string $languageCodeB,
        VersionInfo $versionInfoA,
        ?VersionInfo $versionInfoB
    ): FormInterface {
        return $this->formFactory->create(
            VersionComparisonType::class,
            new VersionComparisonData(
                new VersionChoiceData(
                    $versionInfoA,
                    $languageCodeA ?? $versionInfoA->initialLanguageCode,
                ),
                new VersionChoiceData(
                    $versionInfoB,
                    $languageCodeB ? $languageCodeB : ($versionInfoB ? $versionInfoB->initialLanguageCode : null),
                )
            ),
            [
                'content_info' => $contentInfo,
            ]
        );
    }
}
