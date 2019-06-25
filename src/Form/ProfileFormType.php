<?php


namespace App\Form;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfileFormType;
use Symfony\Component\Form\FormBuilderInterface;


class ProfileFormType extends BaseProfileFormType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    parent::buildForm($builder, $options);
    if ($builder->has('username')) {
      $builder->remove('username');
    }
  }
}
