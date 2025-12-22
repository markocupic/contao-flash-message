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

namespace Markocupic\ContaoFlashMessage\FlashMessage\Trait;

trait ConfirmTrait
{
    public const TYPE_CONFIRM = 'CONFIRM';

    public function addConfirm(string $message): void
    {
        $this->add($message, self::TYPE_CONFIRM);
    }

    public function hasConfirm(): bool
    {
        return $this->has(self::TYPE_CONFIRM);
    }

    public function getConfirm(): array
    {
        return $this->get(self::TYPE_CONFIRM);
    }
}
