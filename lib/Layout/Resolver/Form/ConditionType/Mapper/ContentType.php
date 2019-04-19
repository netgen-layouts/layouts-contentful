<?php

declare(strict_types=1);

namespace Netgen\Layouts\Contentful\Layout\Resolver\Form\ConditionType\Mapper;

use Generator;
use Netgen\Layouts\Contentful\Service\Contentful;
use Netgen\Layouts\Layout\Resolver\Form\ConditionType\Mapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class ContentType extends Mapper
{
    /**
     * @var \Netgen\Layouts\Contentful\Service\Contentful
     */
    private $contentful;

    public function __construct(Contentful $contentful)
    {
        $this->contentful = $contentful;
    }

    public function getFormType(): string
    {
        return ChoiceType::class;
    }

    public function getFormOptions(): array
    {
        return [
            'choices' => (function (): Generator {
                foreach ($this->contentful->getClients() as $client) {
                    $contentTypes = [];

                    /** @var \Contentful\Delivery\Resource\ContentType $contentType */
                    foreach ($client->getContentTypes()->getItems() as $contentType) {
                        $contentTypes[$contentType->getName()] = $contentType->getId();
                    }

                    yield $client->getSpace()->getName() => $contentTypes;
                }
            })(),
            'multiple' => true,
        ];
    }
}
