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

namespace Markocupic\ContaoFlashMessage\Tests\FlashMessage;

use Markocupic\ContaoFlashMessage\FlashMessage\Message;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AbstractMessageTest extends TestCase
{
    private RequestStack $requestStack;

    private SessionInterface $session;

    private FlashBagInterface $flashBag;

    private Message $message;

    protected function setUp(): void
    {
        $this->flashBag = $this->createMock(FlashBag::class);
        $this->session = $this->createMock(Session::class);
        $this->session
            ->method('getFlashBag')
            ->willReturn($this->flashBag)
        ;
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack
            ->method('getSession')
            ->willReturn($this->session)
        ;

        $this->message = new Message($this->requestStack, 'default');
    }

    public function testHasReturnsTrueWhenTypeIsPresent(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->expects($this->once())
            ->method('has')
            ->with('mc_flash_message.default.error')
            ->willReturn(true)
        ;

        $this->assertTrue($this->message->has(Message::TYPE_ERROR));
    }

    public function testHasReturnsFalseWhenTypeIsNotPresent(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->expects($this->once())
            ->method('has')
            ->with('mc_flash_message.default.info')
            ->willReturn(false)
        ;

        $this->assertFalse($this->message->has(Message::TYPE_INFO));
    }

    public function testHasThrowsExceptionForInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid message type INVALID.');

        $this->message->has('INVALID');
    }

    public function testHasReturnsFalseWhenSessionIsNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('has')
        ;

        $this->assertFalse($this->message->has(Message::TYPE_SUCCESS));
    }

    public function testAddSuccessfullyAddsMessageToFlashBag(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->expects($this->once())
            ->method('add')
            ->with('mc_flash_message.default.success', 'Test message')
        ;

        $this->message->add('Test message', Message::TYPE_SUCCESS);
    }

    public function testAddWithEmptyMessageDoesNothing(): void
    {
        $this->flashBag
            ->expects($this->never())
            ->method('add')
        ;

        $this->message->add('', Message::TYPE_SUCCESS);
    }

    public function testAddThrowsExceptionForInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid message type INVALID.');

        $this->message->add('Test message', 'INVALID');
    }

    public function testGetReturnsEmptyArrayWhenSessionNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('get')
        ;

        $this->assertSame([], $this->message->get(Message::TYPE_INFO));
    }

    public function testGetThrowsExceptionForInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid message type INVALID.');

        $this->message->get('INVALID');
    }

    public function testGetReturnsMessagesForValidType(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->expects($this->once())
            ->method('get')
            ->with('mc_flash_message.default.error')
            ->willReturn(['Test Error Message', 'Duplicate Error Message', 'Duplicate Error Message'])
        ;

        $this->assertSame(['Test Error Message', 'Duplicate Error Message'], $this->message->get(Message::TYPE_ERROR));
    }

    public function testGetAllReturnsEmptyArrayWhenSessionNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('get')
        ;

        $this->assertSame([], $this->message->getAll());
    }

    public function testGetAllReturnsMessagesGroupedByType(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('has')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => true,
                        'mc_flash_message.default.error' => true,
                        default => false,
                    };
                },
            )
        ;

        $this->flashBag
            ->method('get')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => ['Success Message 1', 'Success Message 2'],
                        'mc_flash_message.default.error' => ['Error Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = [
            Message::TYPE_ERROR => ['Error Message'],
            Message::TYPE_SUCCESS => ['Success Message 1', 'Success Message 2'],
        ];

        $this->assertSame($expected, $this->message->getAll());
    }

    public function testGetAllIgnoresDuplicateMessages(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('has')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => true,
                        'mc_flash_message.default.info' => true,
                        default => false,
                    };
                },
            )
        ;

        $this->flashBag
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => ['Duplicate Message', 'Duplicate Message'],
                        'mc_flash_message.default.info' => ['Info Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = [
            Message::TYPE_INFO => ['Info Message'],
            Message::TYPE_SUCCESS => ['Duplicate Message'],
        ];

        $this->assertSame($expected, $this->message->getAll());
    }

    public function testPeekReturnsMessagesForValidType(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->expects($this->once())
            ->method('peek')
            ->with('mc_flash_message.default.info')
            ->willReturn(['Info Message', 'Another Info Message'])
        ;

        $this->assertSame(['Info Message', 'Another Info Message'], $this->message->peek(Message::TYPE_INFO));
    }

    public function testPeekReturnsEmptyArrayWhenSessionNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('peek')
        ;

        $this->assertSame([], $this->message->peek(Message::TYPE_WARNING));
    }

    public function testPeekThrowsExceptionForInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid message type INVALID.');

        $this->message->peek('INVALID');
    }

    public function testPeekAllReturnsEmptyArrayWhenSessionNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('get')
        ;

        $this->assertSame([], $this->message->peekAll());
    }

    public function testPeekAllReturnsMessagesGroupedByType(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('has')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => true,
                        'mc_flash_message.default.error' => true,
                        default => false,
                    };
                },
            )
        ;

        $this->flashBag
            ->method('peek')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => ['Success Message 1', 'Success Message 2'],
                        'mc_flash_message.default.error' => ['Error Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = [
            Message::TYPE_ERROR => ['Error Message'],
            Message::TYPE_SUCCESS => ['Success Message 1', 'Success Message 2'],
        ];

        $this->assertSame($expected, $this->message->peekAll());
    }

    public function testPeekAllIgnoresDuplicateMessages(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('has')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => true,
                        'mc_flash_message.default.info' => true,
                        default => false,
                    };
                },
            )
        ;

        $this->flashBag
            ->expects($this->atLeastOnce())
            ->method('peek')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => ['Duplicate Message', 'Duplicate Message'],
                        'mc_flash_message.default.info' => ['Info Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = [
            Message::TYPE_INFO => ['Info Message'],
            Message::TYPE_SUCCESS => ['Duplicate Message'],
        ];

        $this->assertSame($expected, $this->message->peekAll());
    }

    public function testRenderReturnsMessagesWithContainer(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('get')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => ['Success Message'],
                        'mc_flash_message.default.error' => ['Error Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = '<div class="tl_message"><p class="tl_error">Error Message</p><p class="tl_success">Success Message</p></div>';

        $this->assertSame($expected, $this->message->render());
    }

    public function testRenderReturnsEmptyStringWhenSessionIsNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('get')
        ;

        $this->assertSame('', $this->message->render());
    }

    public function testRenderUsesPeekForPreview(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('peek')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.info' => ['Info Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = '<div class="tl_message"><p class="tl_info">Info Message</p></div>';

        $this->assertSame($expected, $this->message->render(true));
    }

    public function testRenderUnwrappedReturnsMessagesWithoutContainer(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('get')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.success' => ['Success Message'],
                        'mc_flash_message.default.error' => ['Error Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = '<p class="tl_error">Error Message</p><p class="tl_success">Success Message</p>';

        $this->assertSame($expected, $this->message->renderUnwrapped());
    }

    public function testRenderUnwrappedUsesPeekToPreviewMessages(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->method('peek')
            ->willReturnCallback(
                static function ($key) {
                    return match ($key) {
                        'mc_flash_message.default.info' => ['Info Message'],
                        default => [],
                    };
                },
            )
        ;

        $expected = '<p class="tl_info">Info Message</p>';

        $this->assertSame($expected, $this->message->renderUnwrapped(true));
    }

    public function testResetClearsAllMessages(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(true)
        ;

        $this->flashBag
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['mc_flash_message.default.error'],
                ['mc_flash_message.default.success'],
            )
        ;

        // Add some dummy keys matching the pattern
        $this->flashBag
            ->method('keys')
            ->willReturn(['mc_flash_message.default.error', 'mc_flash_message.default.success'])
        ;

        $this->message->reset();
    }

    public function testResetDoesNothingWhenSessionNotStarted(): void
    {
        $this->session
            ->method('isStarted')
            ->willReturn(false)
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('keys')
        ;

        $this->flashBag
            ->expects($this->never())
            ->method('get')
        ;

        $this->message->reset();
    }

    public function testGetFlashBagKeyForTypeReturnsCorrectKey(): void
    {
        $result = $this->invokeMethod($this->message, 'getFlashBagKeyForType', [Message::TYPE_ERROR]);
        $this->assertSame('mc_flash_message.default.error', $result);
    }

    public function testGetFlashBagKeyForTypeThrowsExceptionForInvalidType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid message type INVALID.');

        $this->invokeMethod($this->message, 'getFlashBagKeyForType', ['INVALID']);
    }

    private function invokeMethod(object $object, string $method, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($method);

        return $method->invokeArgs($object, $parameters);
    }
}
