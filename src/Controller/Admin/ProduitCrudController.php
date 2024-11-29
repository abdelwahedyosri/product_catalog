<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre'),
            TextEditorField::new('description', 'Description'),
            MoneyField::new('price', 'Prix')->setCurrency('EUR'),
            MoneyField::new('salePrice', 'Prix Promo')->setCurrency('EUR')->hideOnIndex(),
            TextField::new('brand', 'Marque'),
            TextField::new('link', 'Lien')->hideOnIndex(),
            AssociationField::new('categorie', 'Catégorie'),
        ];

        if ($pageName === 'index') {
            // Display the image on the index page
            $fields[] = ImageField::new('imageLink', 'Image')
                ->setBasePath('') // External image URL
                ->setLabel('Image')
                ->onlyOnIndex();
        } else {
            // Allow editing the image link as a text field
            $fields[] = TextField::new('imageLink', 'Image URL')->onlyOnForms();
        }

        return $fields;
    }
}
