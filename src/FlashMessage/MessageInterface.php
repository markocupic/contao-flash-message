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

interface MessageInterface
{
    public function getName(): string;

    public function getTypes(): array;

    public function has(string $type): bool;

    public function hasMessages(): bool;

    public function add(string $message, string $type): void;

    public function get(string $type): array;

    public function getAll(): array;

    public function peek(string $type): array;

    public function peekAll(): array;

    public function render(bool $peek = false): string;

    public function renderUnwrapped(bool $peek = false): string;

    public function reset(): void;
}
