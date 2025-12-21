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

namespace Markocupic\ContaoFlashMessage\FlashMessage;

use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractMessage
{
    public function __construct(
        protected readonly RequestStack $requestStack,
    ) {
    }

    public function has(string $type): bool
    {
        if (!\in_array($type, $this->getTypes(), true)) {
            throw new \Exception(\sprintf('Invalid message type %s.', $type));
        }

        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return false;
        }

        return $session->getFlashBag()->has($this->getFlashBagKeyForType($type));
    }

    public function hasMessages(): bool
    {
        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return false;
        }

        $types = $this->getTypes();

        foreach ($types as $type) {
            if ($session->getFlashBag()->has($this->getFlashBagKeyForType($type))) {
                return true;
            }
        }

        return false;
    }

    public function add(string $message, string $type): void
    {
        if (!$message) {
            return;
        }

        if (!\in_array($type, $this->getTypes(), true)) {
            throw new \Exception(\sprintf('Invalid message type %s.', $type));
        }

        $this->requestStack->getSession()->getFlashBag()->add($this->getFlashBagKeyForType($type), $message);
    }

    public function get(string $type): array
    {
        if (!\in_array($type, $this->getTypes(), true)) {
            throw new \Exception(\sprintf('Invalid message type %s.', $type));
        }

        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return [];
        }

        $flashBag = $session->getFlashBag();

        return array_unique($flashBag->get($this->getFlashBagKeyForType($type)));
    }

    public function getAll(): array
    {
        $messages = [];
        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return $messages;
        }

        $flashBag = $session->getFlashBag();

        foreach ($this->getTypes() as $type) {
            if (!$flashBag->has($this->getFlashBagKeyForType($type))) {
                continue;
            }

            $messages[$type] = array_unique($flashBag->get($this->getFlashBagKeyForType($type)));
        }

        return $messages;
    }

    /**
     * Return the messages as an array.
     */
    public function peek(string $type): array
    {
        if (!\in_array($type, $this->getTypes(), true)) {
            throw new \Exception(\sprintf('Invalid message type %s.', $type));
        }

        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return [];
        }

        $flashBag = $session->getFlashBag();

        return array_unique($flashBag->peek($this->getFlashBagKeyForType($type)));
    }

    /**
     * Return the messages as an array.
     */
    public function peekAll(): array
    {
        $messages = [];
        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return $messages;
        }

        $flashBag = $session->getFlashBag();

        foreach ($this->getTypes() as $type) {
            if (!$flashBag->has($this->getFlashBagKeyForType($type))) {
                continue;
            }

            $messages[$type] = array_unique($flashBag->peek($this->getFlashBagKeyForType($type)));
        }

        return $messages;
    }

    /**
     * Return the messages with a wrapping container as HTML.
     */
    public function generate(bool $peek = false): string
    {
        $messages = $this->generateUnwrapped($peek);

        if ($messages) {
            $messages = '<div class="tl_message">'.$messages.'</div>';
        }

        return $messages;
    }

    /**
     * Return the messages as HTML.
     */
    public function generateUnwrapped(bool $peek = false): string
    {
        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return '';
        }

        $strMessages = '';
        $flashBag = $session->getFlashBag();

        foreach ($this->getTypes() as $type) {
            $class = strtolower($type);

            if ($peek) {
                $arrMessages = $flashBag->peek($this->getFlashBagKeyForType($type));
            } else {
                $arrMessages = $flashBag->get($this->getFlashBagKeyForType($type));
            }

            foreach (array_unique($arrMessages) as $message) {
                if ('RAW' === $type) {
                    $strMessages .= $message;
                } else {
                    $strMessages .= '<p class="tl_'.$class.'">'.$message.'</p>';
                }
            }
        }

        return trim($strMessages);
    }

    /**
     * Reset the message system.
     */
    public function reset(): void
    {
        $session = $this->requestStack->getSession();

        if (!$session->isStarted()) {
            return;
        }

        $flashBag = $session->getFlashBag();

        // Find all contao. keys (see #3393)
        $keys = preg_grep('(^mc_flash_message\.'.static::getName().'\.)', $flashBag->keys());

        foreach ($keys as $key) {
            $flashBag->get($key); // clears the message
        }
    }

    /**
     * Return the flash bag key e.g. "mc_flash_message.generic.error".
     */
    protected function getFlashBagKeyForType(string $type): string
    {
        if (!\in_array($type, $this->getTypes(), true)) {
            throw new \Exception(\sprintf('Invalid message type %s.', $type));
        }

        return 'mc_flash_message.'.static::getName().'.'.strtolower($type);
    }
}
