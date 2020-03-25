<?php
/**
 * Created by PhpStorm.
 * User: INFO
 * Date: 17/07/2018
 * Time: 14:50
 */

namespace UtilisateurBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class PasswordChangeType extends AbstractType
{
    /**
     * Formulare pour changement mot de passe
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe doivent être identiques.',
            'options' => array('attr' => array('class' => 'new-password')),
            'required' => true,
            'first_options' => array(
                'label' => 'Nouveau mot de passe',
                'attr' => array('autofocus' => true),
            ),
            'second_options' => array('label' => 'Confirmer'),
            'constraints' => array(
                new Assert\Length(array(
                    'min' => 4,
                    'minMessage' => 'Mot de passe trop court. Minimum 4 caractères.',
                )),
                new Assert\NotBlank(array(
                    'message' => 'Le mot de passe ne doit pas être vide.'
                )),
            ),
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }
}