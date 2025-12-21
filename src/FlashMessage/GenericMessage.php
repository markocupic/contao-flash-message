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

class GenericMessage extends AbstractMessage
{
    public const TYPE_ERROR = 'ERROR';

    public const TYPE_WARNING = 'WARNING';

    public const TYPE_CONFIRM = 'CONFIRM';

    public const TYPE_NEW = 'NEW';

    public const TYPE_INFO = 'INFO';

    public const TYPE_SUCCESS = 'SUCCESS';

    public const TYPE_RAW = 'RAW';

    public static function getName(): string
    {
        return 'generic';
    }

    /**
     * Return all available message types.
     */
    public function getTypes(): array
    {
        return [self::TYPE_ERROR, self::TYPE_WARNING, self::TYPE_CONFIRM, self::TYPE_NEW, self::TYPE_INFO, self::TYPE_SUCCESS, self::TYPE_RAW];
    }

    public function addError(string $message): void
    {
        $this->add($message, self::TYPE_ERROR);
    }

    public function addWarning(string $message): void
    {
        $this->add($message, self::TYPE_WARNING);
    }

    public function addConfirmation(string $message): void
    {
        $this->add($message, self::TYPE_CONFIRM);
    }

    public function addNew(string $message): void
    {
        $this->add($message, self::TYPE_NEW);
    }

    public function addInfo(string $message): void
    {
        $this->add($message, self::TYPE_INFO);
    }

    public function addSuccess(string $message): void
    {
        $this->add($message, self::TYPE_SUCCESS);
    }

    public function addRaw(string $message): void
    {
        $this->add($message, self::TYPE_RAW);
    }

    public function hasError(): bool
    {
        return $this->has(self::TYPE_ERROR);
    }

    public function hasWarning(): bool
    {
        return $this->has(self::TYPE_WARNING);
    }

    public function hasConfirmation(): bool
    {
        return $this->has(self::TYPE_CONFIRM);
    }

    public function hasNew(): bool
    {
        return $this->has(self::TYPE_NEW);
    }

    public function hasInfo(): bool
    {
        return $this->has(self::TYPE_INFO);
    }

    public function hasSuccess(): bool
    {
        return $this->has(self::TYPE_SUCCESS);
    }

    public function hasRaw(): bool
    {
        return $this->has(self::TYPE_RAW);
    }
}
