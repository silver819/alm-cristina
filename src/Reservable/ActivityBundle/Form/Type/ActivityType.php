<?php

namespace Reservable\ActivityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Reservable\ActivityBundle\Entity\Activity;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActivityType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 			'text', 		['label' 		=> 'registerActivity.labels.name']);

        $builder->add('typeRent', 		'choice', 		['label' 		=> 'registerActivity.labels.type',
        									 			 'choices' 		=> array('hour'	=> 'registerActivity.labels.hour', 
        									 			 					     'day' 	=> 'registerActivity.labels.day')]);

        $builder->add('zone', 		    'choice', 		['label' 		=> 'registerActivity.labels.zone',
                                                         'choices' 		=> $options['attr']['zones'],
                                                         'required'     => true]);

        $builder->add('address', 		'text', 		['label' 		=> 'registerActivity.labels.address']);

        $builder->add('description',	'textarea', 	['label' 		=> 'registerActivity.labels.description']);

		$builder->add('pictures',		'collection',	['label' 		=> 'registerActivity.labels.picture',
												 		 'type' 		=> new PictureType(), 
												 		 'allow_add'    => true,
        												 'by_reference' => false,
        												 'allow_delete' => true,
                    									 'prototype' 	=> true,
                    									 'options' 		=> array('label' => false)]);

        $builder->add('ownerID','hidden');
        $builder->add('lat','hidden');
        $builder->add('lng','hidden');
        $builder->add('active','hidden');
	}

	public function getName()
	{
		return 'activity';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array('data_class' => 'Reservable\ActivityBundle\Entity\Activity',
									 'csrf_protection' => true,
									 'csrf_field_name' => '_token',
									 'intention'       => 'task_item'
			));
	}
}