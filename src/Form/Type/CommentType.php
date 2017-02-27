<?php

namespace Alaska\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('author', TextType::class);
        $builder->add('content', TextareaType::class);
        $builder->add('parentId', TextareaType::class);
    }

    public function getName()
    {
        return 'comment';
    }
}
