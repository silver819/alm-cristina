<?php
namespace Reservable\ActivityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Reservable\ActivityBundle\Document\Picture;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PictureType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('file', 'file', array('required' => true));
		$builder->add('activityID', 'hidden');
	}

	public function getName()
	{
		return 'picture';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(
			array(
				'data_class' 		=> 'Reservable\ActivityBundle\Entity\Picture',
				'csrf_protection' 	=> true,
				'csrf_field_name' 	=> '_token',
				'intention'       	=> 'task_item'
			)
		);
	}
}