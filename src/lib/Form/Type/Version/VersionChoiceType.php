<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Form\Type\Version;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use EzSystems\EzPlatformAdminUi\Form\Data\Version\VersionChoiceData;
use EzSystems\EzPlatformAdminUi\Form\DataMapper\VersionChoiceMapper;
use EzSystems\EzPlatformAdminUi\Form\DataTransformer\VersionInfoComparisonTransformer;
use EzSystems\EzPlatformAdminUi\Form\Type\ChoiceList\Loader\VersionInfoChoiceLoader;
use EzSystems\EzPlatformAdminUi\UI\Service\VersionStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class VersionChoiceType extends AbstractType
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \EzSystems\EzPlatformAdminUi\UI\Service\VersionStatus */
    private $versionStatus;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /** @var \eZ\Publish\API\Repository\LanguageService */
    private $languageService;

    public function __construct(
        ContentService $contentService,
        VersionStatus $versionStatus,
        TranslatorInterface $translator,
        LanguageService $languageService
    ) {
        $this->contentService = $contentService;
        $this->versionStatus = $versionStatus;
        $this->translator = $translator;
        $this->languageService = $languageService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'version',
            ChoiceType::class,
            [
                'choice_loader' => new VersionInfoChoiceLoader(
                    $this->contentService,
                    $this->versionStatus,
                    $this->translator,
                    $this->languageService,
                    $options['content_info']
                ),
                'choice_attr' => function ($choice, $key, $value) {
                    $data = explode('/', $value);
                    return [
                        'data-version-no' => $data[0],
                        'data-language-code' => $data[1],
                    ];
                },
                /** @Desc("Select a version to compare") */
                'placeholder' => $this->translator->trans('version_info.comparison.select_placeholder', [], 'version'),
                'label' => false,
            ]
        )
            ->setDataMapper(new VersionChoiceMapper());
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault(
                'data_class', VersionChoiceData::class
            )
            ->setRequired(
                'content_info'
            )
            ->setAllowedTypes(
                'content_info', ContentInfo::class
            );
    }
}
