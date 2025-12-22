![Alt text](docs/logo.png?raw=true "logo")

# Welcome to Flash Bag Message

Dieses Symfony Bundle ermöglicht es, Flash Messages in einer Anwendung zu verwalten. Mit sehr einfachen Mitteln kann für jeden Controller ein eigener
Message Handler eingerichtet werden, welcher die Flash Messages unter einem eigenen Subkey speichert. Dadurch wird verhindert, dass Messages, welche von anderen Controllern generiert wurden, vermischt werden.
Alle Messages werden in ihrem eigenen Subkey/Namespace abgelegt.

Um einen neuen Message-Handler zu erzeugen, muss nur eine neue Message-Klasse erstellt werden, welche `Markocupic\ContaoFlashMessage\FlashMessage\MessageInterface` implementiert.

```php
<?php

namespace App\FlashMessage\Checkout;

use Markocupic\ContaoFlashMessage\FlashMessage\GenericMessage;
use Markocupic\ContaoFlashMessage\FlashMessage\MessageInterface;

class Message extends GenericMessage implements MessageInterface
{
    /**
     * Return the flash subkey.
     */
    public static function getName(): string
    {
        return 'shop_checkout'; // Has to be unique!
    }
}

```

Der Message-Handler ist nun als Service registriert und kann mittels Dependency Injection eingebunden werden.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\App\FlashMessage\Checkout\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment as Twig;

#[Route('/shop/shop_checkout', name: ShopCheckoutController::class, defaults: ['_scope' => 'frontend', '_token_check' => true])]
class ShopCheckoutController extends AbstractController
{
    public function __construct(
        private readonly Message $message,
        private readonly Twig $twig,
    ) {
    }

    public function __invoke(): Response
    {
        $this->message->addError('This is an error message.');
        $this->message->addInfo('This is an info message.');
        $this->message->addSuccess('This is a success message.');

        return new Response($this->twig->render(
            '@App/Shop/checkout.html.twig',
            [
                'messages' => $this->message->generate(),
            ],
        ));
    }
}
```

```twig
{# Twig template #}

<div class="messages">
    {{ messages|raw }}
</div>
```

## Messages in Twig direkt und strukturiert abfragen

Messages lassen sich auch direkt (und selektiv/strukturiert) in Twig abgefragen. Dazu muss der namespace angegeben werden.

```twig
{# Twig template #}

{% if has_flash_messages('shop_checkout') %}
    <div class="messages">
        {% set messages = get_flash_messages('shop_checkout') %}
        {% for type, message_group in messages %}
            {% for message in messages[type] %}
                <div class="tl_{{ type|lower }}">
                    {{ message }}
                </div>
            {% endfor %}
        {% endfor %}
    </div>
{% endif %}
```
