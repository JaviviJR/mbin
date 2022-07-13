<?php declare(strict_types=1);

namespace App\EventSubscriber\ActivityPub;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\ActivityPub\JsonRdLink;
use App\ActivityPub\WebfingerParameters;
use App\Event\ActivityPub\WebfingerResponseEvent;
use App\Repository\UserRepository;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class WebfingerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private WebfingerParameters $webfingerParameters,
        private UserRepository $userRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    #[ArrayShape([WebfingerResponseEvent::class => "string"])] public static function getSubscribedEvents(): array
    {
        return [
            WebfingerResponseEvent::class => ['buildResponse', 1000],
        ];
    }

    public function buildResponse(WebfingerResponseEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $params  = $this->webfingerParameters->getParams();
        $jsonRd  = $event->jsonRd;

        $subject = $request->query->get('resource') ?: '';
        if (!empty($subject)) {
            $jsonRd->setSubject($subject);
        }

        if (isset($params[WebfingerParameters::ACCOUNT_KEY_NAME]) && $actor = $this->getActor($params[WebfingerParameters::ACCOUNT_KEY_NAME])) {
            $accountHref = $this->urlGenerator->generate(
                'ap_user',
                ['username' => $actor->getUserIdentifier()],
                UrlGeneratorInterface::ABS_URL
            );

            $jsonRd->addAlias($accountHref);
            $link = new JsonRdLink();
            $link->setRel('http://webfinger.net/rel/profile-page')
                ->setType('text/html')
                ->setHref($accountHref);
            $jsonRd->addLink($link);
        }
    }

    protected function getActor($name): ?UserInterface
    {
        return $this->userRepository->findOneByUsername($name);
    }
}
