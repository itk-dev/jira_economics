<?php


namespace App\Form;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;


class RegistrationFormType extends BaseRegistrationFormType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    parent::buildForm($builder, $options);
    if ($builder->has('username')) {
      $builder->remove('username');
    }
  }
}
