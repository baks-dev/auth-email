<?php

namespace BaksDev\Auth\Email\UseCase\User\Login;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class LoginForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options) : void
	{
		/*
		 * Email
		 */
		$builder->add
		(
			'email',
			EmailType::class,
			[
				'attr' => ['autocomplete' => 'email'],
			]
		);
		
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
		
		/*
		 * Пароль
		 */
		$builder->add('password', PasswordType::class, [
			'attr' => ['autocomplete' => 'new-password'],
		]);
		
		/* Применить ******************************************************/
		$builder->add
		(
			'login',
			SubmitType::class,
			['label' => 'Login', 'label_html' => true]
		);
	}
	
	
	public function configureOptions(OptionsResolver $resolver) : void
	{
		$resolver->setDefaults
		(
			[
				'data_class' => LoginDTO::class,
				'translation_domain' => 'user.login',
				'csrf_token_id' => 'authenticate',
			]
		);
	}
	
}
