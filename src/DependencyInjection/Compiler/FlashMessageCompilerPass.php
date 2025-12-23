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

namespace Markocupic\ContaoFlashMessage\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlashMessageCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('markocupic_contao_flash_message.flash_message.locator')) {
            return;
        }

        $locatorMap = [];

        $messageIds = [];

        foreach ($container->findTaggedServiceIds('mc.flash_message.message_handler') as $id => $tags) {
            if (\count($tags) > 1) {
                throw new \InvalidArgumentException(\sprintf('The service with the serviceId "%s" has multiple tags "mc.flash_message.message_handler". Only one tag of this type is allowed.', $id));
            }

            $definition = $container->getDefinition($id);

            // You can have multiple tags; we take the first one
            $messageId = $tags[0]['messageId'] ?? null;

            if (null === $messageId) {
                throw new \InvalidArgumentException(\sprintf('The tag "mc.flash_message.message_handler" requires a "messageId" attribute for the service with the serviceId "%s".', $id));
            }

            if (\in_array($messageId, $messageIds, true)) {
                throw new \InvalidArgumentException(\sprintf('The messageId "%s" is already in use. The tag "mc.flash_message.message_handler" requires a unique "messageId" attribute for the service with the serviceId %s.', $messageId, $id));
            }

            // Inject the message id into the constructor
            $definition->setArgument('$name', $messageId);

            // Use the message id as the key in the locator
            $locatorMap[$messageId] = new Reference($id);
        }

        // Replace the locator argument with the indexed map
        $container->getDefinition('markocupic_contao_flash_message.flash_message.locator')
            ->setArgument(0, $locatorMap)
        ;
    }
}
