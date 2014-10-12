<?php

namespace User\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add your custom field
        $builder->add('name');
        $builder->add('surname');
        $builder->add('phoneNumber');
        $builder->add('mobileNumber');
    }

    public function getParent()
    {
        return 'fos_user_profile_edit';
    }

    public function getName()
    {
        return 'user_edit_profile';
    }
}