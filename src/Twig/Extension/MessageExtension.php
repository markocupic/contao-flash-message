<?php

declare(strict_types=1);

/*
 * This file is part of Flash Bag Message.
 *
 * (c) Marko Cupic <m.cupic@gmx.ch>
 * @license GPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/contao-flash-message
 */

namespace Markocupic\ContaoFlashMessage\Twig\Extension;

use Markocupic\ContaoTranslationBundle\Message\Message;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MessageExtension extends AbstractExtension
{
    public function __construct(
        #[AutowireLocator('mc.flash_message.message_handler', defaultIndexMethod: 'getName')]
        private ContainerInterface $messageHandlers,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_messages', [$this, 'getMessages']),
        ];
    }

    public function getMessages(string $messageSystem = 'generic'): array
    {
        if (!$this->messageHandlers->has($messageSystem)) {
            throw new \Exception('Message system '.$messageSystem.' not found.');
        }

        /** @var Message $message */
        $message = $this->messageHandlers->get($messageSystem);

        return $message->getAll();
    }

    private function getMessageHandlers(): array
    {
        return $this->messageHandlers->getProvidedServices();
    }
}
