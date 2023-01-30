<?php

namespace BaksDev\Auth\Email\UseCase\User\Restore;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\UseCase\User\Registration\RegistrationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RestoreForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		/** Email */
		$builder->add('email', EmailType::class);
		
		$builder->get('email')->addModelTransformer(
			new CallbackTransformer(
				function($email) {
					return $email instanceof AccountEmail ? $email->getValue() : $email;
				},
				function($email) {
					
					return new AccountEmail($email);
				}
			)
		);
		
		/** Восстановить */
		$builder->add
		(
			'restore',
			SubmitType::class,
			['label' => 'Restore', 'label_html' => true]
		);
		
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults
		(
			[
				'data_class' => RestoreDTO::class,
				'attr' => ['autocomplete' => "off"],
			]
		);
	}
	
}
