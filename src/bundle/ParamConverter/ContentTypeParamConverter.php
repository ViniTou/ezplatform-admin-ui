<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUiBundle\ParamConverter;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentTypeParamConverter implements ParamConverterInterface
{
    const PARAMETER_CONTENT_TYPE_ID = 'contentTypeId';
    const PARAMETER_CONTENT_TYPE_IDENTIFIER = 'contentTypeIdentifier';

    /** @var ContentTypeService */
    private $contentTypeService;

    /** @var array */
    private $siteAccessLanguages;

    /**
     * @param ContentTypeService $contentTypeGroupService
     * @param array $siteAccessLanguages
     */
    public function __construct(ContentTypeService $contentTypeGroupService, array $siteAccessLanguages)
    {
        $this->contentTypeService = $contentTypeGroupService;
        $this->siteAccessLanguages = $siteAccessLanguages;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (!$request->get(self::PARAMETER_CONTENT_TYPE_ID) && !$request->get(self::PARAMETER_CONTENT_TYPE_IDENTIFIER)) {
            return false;
        }
        $prioritizedLanguages = $this->siteAccessLanguages;
        if ($request->get(LanguageParamConverter::PARAMETER_LANGUAGE_CODE)) {
            array_unshift(
                $prioritizedLanguages,
                $request->get(LanguageParamConverter::PARAMETER_LANGUAGE_CODE)
            );
        }

        if ($request->get(self::PARAMETER_CONTENT_TYPE_ID)) {
            $id = (int)$request->get(self::PARAMETER_CONTENT_TYPE_ID);
            $contentType = $this->contentTypeService->loadContentType($id, $prioritizedLanguages);
        } elseif ($request->get(self::PARAMETER_CONTENT_TYPE_IDENTIFIER)) {
            $identifier = $request->get(self::PARAMETER_CONTENT_TYPE_IDENTIFIER);
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($identifier, $prioritizedLanguages);
        }

        if (!$contentType) {
            throw new NotFoundHttpException('ContentType ' . ($id ?? $identifier) . ' not found!');
        }

        $request->attributes->set($configuration->getName(), $contentType);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return ContentType::class === $configuration->getClass();
    }
}
