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

trait SuccessTrait
{
    public const TYPE_SUCCESS = 'SUCCESS';

    public function addSuccess(string $message): void
    {
        $this->add($message, self::TYPE_SUCCESS);
    }

    public function hasSuccess(): bool
    {
        return $this->has(self::TYPE_SUCCESS);
    }

    public function getSuccess(): array
    {
        return $this->get(self::TYPE_SUCCESS);
    }
}
