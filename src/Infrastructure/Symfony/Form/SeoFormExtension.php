<?php

declare(strict_types=1);

namespace AlexandreBulete\DddSeoBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddSeoBundle\Application\Command\SaveSeoEntry\SaveSeoEntryCommand;
use AlexandreBulete\DddSeoBundle\Application\Query\FindSeoEntry\FindSeoEntryQuery;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoFields;
use AlexandreBulete\DddSeoBundle\Domain\ValueObject\SeoTarget;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Uid\AbstractUid;

/**
 * Adds a "SEO" block to the back-office form of a content (`seo.forms`), once
 * the content exists: its id is the page's reference. Saved with the form,
 * when it is valid.
 *
 * Extends every form, and acts only on the configured types: a type
 * extension declares its types statically, the configuration does not.
 */
final class SeoFormExtension extends AbstractTypeExtension
{
    public const string FIELD = 'seo';

    /**
     * @param array<class-string, string> $forms form type => kind of page
     */
    public function __construct(
        #[Autowire(param: 'seo.forms')]
        private readonly array $forms,
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
    ) {}

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $kind = $this->forms[$builder->getType()->getInnerType()::class] ?? null;
        if ($kind === null) {
            return;
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($kind): void {
            $reference = self::reference($event->getData());
            if ($reference === null) {
                return;
            }
            /** @var SeoFields $fields */
            $fields = $this->queryBus->ask(new FindSeoEntryQuery(new SeoTarget($kind, $reference)));
            $event->getForm()->add(self::FIELD, SeoFieldsType::class, [
                'mapped' => false,
                'required' => false,
                'data' => SeoFieldsData::fromFields($fields),
            ]);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($kind): void {
            $form = $event->getForm();
            $reference = self::reference($form->getData());
            if ($reference === null || !$form->has(self::FIELD) || !$form->isValid()) {
                return;
            }
            $seo = $form->get(self::FIELD);
            $data = $seo->getData();
            if (!$data instanceof SeoFieldsData) {
                return;
            }
            try {
                $this->commandBus->dispatch(new SaveSeoEntryCommand(new SeoTarget($kind, $reference), $data->toFields()));
            } catch (\InvalidArgumentException $e) {
                self::error($seo, $e->getMessage());
            }
        }, -10);
    }

    /**
     * The content's id, once it has one: a getter or a property `id`.
     */
    private static function reference(mixed $data): ?string
    {
        if (!is_object($data)) {
            return null;
        }
        $id = method_exists($data, 'getId') ? $data->getId() : ($data->id ?? null);
        if ($id instanceof AbstractUid) {
            return $id->toRfc4122();
        }

        $id = is_scalar($id) || $id instanceof \Stringable ? (string) $id : '';

        return $id === '' ? null : $id;
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private static function error(FormInterface $form, string $message): void
    {
        $form->addError(new FormError($message));
    }
}
