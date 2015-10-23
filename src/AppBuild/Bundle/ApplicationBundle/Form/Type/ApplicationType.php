<?php

namespace AppBuild\Bundle\ApplicationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBuild\Bundle\ApplicationBundle\Form\DataTransformer\BuiltApplicationTransformer;

/**
 * Form type for Application entity.
 */
class ApplicationType extends AbstractType
{
    /**
     * @var string
     */
    protected $buildApplicationDir;

    /**
     * construct.
     *
     * @param string $buildApplicationDir
     */
    public function __construct($buildApplicationDir = null)
    {
        $this->buildApplicationDir = $buildApplicationDir;
    }

    /**
     * @see FormInterface::getName()
     */
    public function getName()
    {
        return 'application';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBuild\Bundle\ApplicationBundle\Entity\Application',
            'csrf_protection' => true,
            'allow_extra_fields' => false,
            'cascade_validation' => false,
            'intention' => null,
        ));
    }

    /**
     * @see FormInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required' => true,
            'label' => 'admin.application.label.name',
        ));
        $builder->add('support', 'choice', array(
            'required' => true,
            'label' => 'admin.application.label.support',
            'choices' => array(
                'ios' => 'admin.application.label.ios',
                'android' => 'admin.application.label.android',
            ),
        ));
        $builder->add('version', 'text', array(
            'required' => true,
            'label' => 'admin.application.label.version',
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $form = $event->getForm();

            if ($this->buildApplicationDir
                && $application = $event->getData()
            ) {
                $formType = $builder->create('filePath', 'file', array(
                    'required' => false,
                    'label' => 'admin.application.label.builder',
                    'auto_initialize' => false,
                ));
                $formType->addModelTransformer(
                    new BuiltApplicationTransformer(
                        sprintf('%s/%s',
                            $this->buildApplicationDir,
                            $application->getSlug()
                        ),
                        $application->getFilePath()
                    )
                );

                $form->add($formType->getForm());
            }
        });
    }
}
