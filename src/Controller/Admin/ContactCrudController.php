<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    /**/
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom Complet'),
            EmailField::new('email', 'Email'),
            TextField::new('subject', 'Sujet'),
            TextareaField::new('message', 'Message'),
        ];
    }
    
}
