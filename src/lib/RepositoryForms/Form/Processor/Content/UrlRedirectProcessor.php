<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\RepositoryForms\Form\Processor\Content;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\URLAliasService;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\MVC\Symfony\Routing\SimplifiedRequest;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use EzSystems\EzPlatformAdminUi\Specification\SiteAccess\IsAdmin;
use EzSystems\RepositoryForms\Event\FormActionEvent;
use EzSystems\RepositoryForms\Event\RepositoryFormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class UrlRedirectProcessor implements EventSubscriberInterface
{
    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \eZ\Publish\API\Repository\URLAliasService */
    private $urlAliasService;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess */
    private $siteaccess;

    /** @var array */
    private $siteaccessGroups;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router */
    private $siteaccessRouter;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteaccess
     * @param array $siteaccessGroups
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess\Router $siteaccessRouter
     */
    public function __construct(
        RouterInterface $router,
        URLAliasService $urlAliasService,
        SiteAccess $siteaccess,
        array $siteaccessGroups,
        SiteAccess\Router $siteaccessRouter
    ) {
        $this->router = $router;
        $this->urlAliasService = $urlAliasService;
        $this->siteaccess = $siteaccess;
        $this->siteaccessGroups = $siteaccessGroups;
        $this->siteaccessRouter = $siteaccessRouter;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RepositoryFormEvents::CONTENT_PUBLISH => ['processRedirectAfterPublish', 0],
            RepositoryFormEvents::CONTENT_CANCEL => ['processRedirectAfterCancel', 0],
        ];
    }

    /**
     * @param \EzSystems\RepositoryForms\Event\FormActionEvent $event
     *
     * @throws \EzSystems\EzPlatformAdminUi\Exception\InvalidArgumentException
     */
    public function processRedirectAfterPublish(FormActionEvent $event): void
    {
        if ($event->getForm()['redirectUrlAfterPublish']->getData()) {
            return;
        }

        $this->resolveAdminSiteaccessRedirect($event);
    }

    /**
     * @param \EzSystems\RepositoryForms\Event\FormActionEvent $event
     *
     * @throws \EzSystems\EzPlatformAdminUi\Exception\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function processRedirectAfterCancel(FormActionEvent $event): void
    {
        $this->resolveAdminSiteaccessRedirect($event);
    }

    /**
     * @return bool
     *
     * @throws \EzSystems\EzPlatformAdminUi\Exception\InvalidArgumentException
     */
    protected function isAdminSiteaccess(): bool
    {
        return (new IsAdmin($this->siteaccessGroups))->isSatisfiedBy($this->siteaccess);
    }

    /**
     * @param \EzSystems\RepositoryForms\Event\FormActionEvent $event
     *
     * @throws \EzSystems\EzPlatformAdminUi\Exception\InvalidArgumentException
     */
    private function resolveAdminSiteaccessRedirect(FormActionEvent $event): void
    {
        if (!$this->isAdminSiteaccess()) {
            return;
        }

        /** @var \Symfony\Component\HttpFoundation\RedirectResponse $response */
        $response = $event->getResponse();

        if (!$response instanceof RedirectResponse) {
            return;
        }

        $simplifiedRequest = SimplifiedRequest::fromUrl($response->getTargetUrl());
        $siteaccess = $this->siteaccessRouter->match($simplifiedRequest);

        $url = $simplifiedRequest->pathinfo;
        if ($siteaccess->matcher instanceof SiteAccess\URILexer) {
            $url = $siteaccess->matcher->analyseURI(parse_url($response->getTargetUrl(), PHP_URL_PATH));
        }
        /* If target URL was set to something else than Location, do nothing */
        try {
            $targetUrlAlias = $this->urlAliasService->lookup($url);
        } catch (InvalidArgumentException $e) {
            return;
        } catch (NotFoundException $e) {
            return;
        }

        if ($targetUrlAlias->type !== URLAlias::LOCATION) {
            return;
        }

        $response = new RedirectResponse($this->router->generate(
            '_ezpublishLocation',
            ['locationId' => $targetUrlAlias->destination],
            UrlGeneratorInterface::ABSOLUTE_URL
        ));
        $event->setResponse($response);
    }
}
