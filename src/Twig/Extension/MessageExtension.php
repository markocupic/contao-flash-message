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

use Markocupic\ContaoFlashMessage\FlashMessage\MessageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MessageExtension extends AbstractExtension
{
    public function __construct(
        #[Autowire(service: 'markocupic_contao_flash_message.flash_message.locator')]
        private readonly ServiceLocator $locator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_flash_messages', [$this, 'hasMessages']),
            new TwigFunction('get_flash_messages', [$this, 'getMessages']),
        ];
    }

    public function hasMessages(string $messageNamespace = 'default'): bool
    {
        if (!$this->locator->has($messageNamespace)) {
            throw new \Exception('Message namespace '.$messageNamespace.' not found.');
        }

        /** @var MessageInterface $message */
        $message = $this->locator->get($messageNamespace);

        if (!$message->hasMessages()) {
            return false;
        }

        return true;
    }

    public function getMessages(string $messageNamespace = 'default'): array
    {
        // die(print_r($this->locator->getProvidedServices(),true));
        if (!$this->locator->has($messageNamespace)) {
            throw new \Exception('Message namespace '.$messageNamespace.' not found.');
        }

        /** @var MessageInterface $message */
        $message = $this->locator->get($messageNamespace);

        return $message->getAll();
    }

    private function getMessageHandlers(): array
    {
        return $this->locator->getProvidedServices();
    }
}
