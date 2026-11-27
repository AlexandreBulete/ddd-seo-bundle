<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\EventListener;

use AlexandreBulete\DddSeoBundle\Application\Command\FollowRedirect\FollowRedirectCommand;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * A page that does not answer, asked for by GET: if a redirect starts from
 * its address, a 301 replaces the 404. Only then, so a redirect never hides
 * a page that exists. The query string follows, unless the target has one.
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 64)]
final readonly class FollowRedirectListener
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()
            || !$event->getThrowable() instanceof NotFoundHttpException
            || !\in_array($request->getMethod(), ['GET', 'HEAD'], true)
        ) {
            return;
        }

        /** @var ?string $target */
        $target = $this->commandBus->dispatch(new FollowRedirectCommand(rawurldecode($request->getPathInfo())));
        if ($target === null) {
            return;
        }
        if (str_starts_with($target, '/')) {
            $target = $request->getBaseUrl() . $target;
        }
        $query = $request->getQueryString();
        if ($query !== null && !str_contains($target, '?')) {
            $target .= '?' . $query;
        }

        $event->setResponse(new RedirectResponse($target, Response::HTTP_MOVED_PERMANENTLY));
    }
}
