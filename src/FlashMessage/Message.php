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

use Markocupic\ContaoFlashMessage\FlashMessage\Trait\ConfirmTrait;
use Markocupic\ContaoFlashMessage\FlashMessage\Trait\ErrorTrait;
use Markocupic\ContaoFlashMessage\FlashMessage\Trait\InfoTrait;
use Markocupic\ContaoFlashMessage\FlashMessage\Trait\NewTrait;
use Markocupic\ContaoFlashMessage\FlashMessage\Trait\RawTrait;
use Markocupic\ContaoFlashMessage\FlashMessage\Trait\SuccessTrait;
use Markocupic\ContaoFlashMessage\FlashMessage\Trait\WarningTrait;

class Message extends AbstractMessage implements MessageInterface
{
    use ConfirmTrait;
    use ErrorTrait;
    use InfoTrait;
    use NewTrait;
    use RawTrait;
    use SuccessTrait;
    use WarningTrait;

    public function getTypes(): array
    {
        return [self::TYPE_CONFIRM, self::TYPE_ERROR, self::TYPE_INFO, self::TYPE_NEW, self::TYPE_RAW, self::TYPE_SUCCESS, self::TYPE_WARNING];
    }
}
